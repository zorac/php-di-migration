<?php

declare(strict_types=1);

namespace DI\Definition\Source;

use DI\Annotation\Inject as InjectAnnotation;
use DI\Annotation\Injectable as InjectableAnnotation;
use DI\Attribute\Inject as InjectAttribute;
use DI\Attribute\Injectable as InjectableAttribute;
use DI\Definition\Exception\InvalidAnnotation;
use DI\Definition\Exception\InvalidAttribute;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Reference;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use InvalidArgumentException;
use PhpDocReader\PhpDocReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use UnexpectedValueException;
use Throwable;

/**
 * Provides DI definitions by reading annotations such as @ Inject and @ var annotations
 * and PHP 8 attributes such as #[Inject] and #[Injectable].
 *
 * Uses Autowiring, Doctrine's Annotations and regex docblock parsing.
 * This source automatically includes the reflection source.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AttributeAndAnnotationBasedAutowiring implements DefinitionSource, Autowiring
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var PhpDocReader
     */
    private $phpDocReader;

    /**
     * @var bool
     */
    private $ignorePhpDocErrors;

    public function __construct($ignorePhpDocErrors = false)
    {
        $this->ignorePhpDocErrors = (bool) $ignorePhpDocErrors;
    }

    public function autowire(string $name, ObjectDefinition $definition = null)
    {
        $className = $definition ? $definition->getClassName() : $name;

        if (!class_exists($className) && !interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ?: new ObjectDefinition($name);

        $class = new ReflectionClass($className);

        $this->readInjectableAnnotation($class, $definition);
        $this->readInjectableAttribute($class, $definition);

        // Browse the class properties looking for annotated properties
        $this->readProperties($class, $definition);

        // Browse the object's methods looking for annotated methods
        $this->readMethods($class, $definition);

        return $definition;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidAnnotation
     * @throws InvalidArgumentException The class doesn't exist
     */
    public function getDefinition(string $name)
    {
        return $this->autowire($name);
    }

    /**
     * Autowiring cannot guess all existing definitions.
     */
    public function getDefinitions() : array
    {
        return [];
    }

    /**
     * Browse the class properties looking for annotated properties.
     */
    private function readProperties(ReflectionClass $class, ObjectDefinition $definition)
    {
        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $this->readPropertyAnnotation($property, $definition);
            $this->readPropertyAttribute($property, $definition);
        }

        // Read also the *private* properties of the parent classes
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($class = $class->getParentClass()) {
            foreach ($class->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
                if ($property->isStatic()) {
                    continue;
                }
                $this->readPropertyAnnotation($property, $definition, $class->getName());
                $this->readPropertyAttribute($property, $definition, $class->getName());
            }
        }
    }

    private function readPropertyAnnotation(ReflectionProperty $property, ObjectDefinition $definition, $classname = null)
    {
        // Look for @Inject annotation
        $annotation = $this->getAnnotationReader()->getPropertyAnnotation($property, 'DI\Annotation\Inject');
        if (!$annotation instanceof InjectAnnotation) {
            return;
        }

        // Try to @Inject("name") or look for @var content
        $entryName = $annotation->getName() ?: $this->getPhpDocReader()->getPropertyClass($property);

        // Try using PHP7.4 typed properties
        if (\PHP_VERSION_ID > 70400
            && $entryName === null
            && $property->getType() instanceof ReflectionNamedType
            && (class_exists($property->getType()->getName()) || interface_exists($property->getType()->getName()))
        ) {
            $entryName = $property->getType()->getName();
        }

        if ($entryName === null) {
            throw new InvalidAnnotation(sprintf(
                '@Inject found on property %s::%s but unable to guess what to inject, use a @var annotation',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            ));
        }

        $definition->addPropertyInjection(
            new PropertyInjection($property->getName(), new Reference($entryName), $classname)
        );
    }

    /**
     * @throws InvalidAttribute
     */
    private function readPropertyAttribute(ReflectionProperty $property, ObjectDefinition $definition, ?string $classname = null) : void
    {
        // Look for #[Inject] attribute
        try {
            $attribute = $property->getAttributes(InjectAttribute::class)[0] ?? null;
            if (! $attribute) {
                return;
            }
            /** @var InjectAttribute $inject */
            $inject = $attribute->newInstance();
        } catch (Throwable $e) {
            throw new InvalidAttribute(sprintf(
                '#[Inject] annotation on property %s::%s is malformed. %s',
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        // Try to #[Inject("name")] or look for the property type
        $entryName = $inject->getName();

        // Try using typed properties
        $propertyType = $property->getType();
        if ($entryName === null && $propertyType instanceof ReflectionNamedType) {
            if (! class_exists($propertyType->getName()) && ! interface_exists($propertyType->getName())) {
                throw new InvalidAttribute(sprintf(
                    '#[Inject] found on property %s::%s but unable to guess what to inject, the type of the property does not look like a valid class or interface name',
                    $property->getDeclaringClass()->getName(),
                    $property->getName()
                ));
            }
            $entryName = $propertyType->getName();
        }

        if ($entryName === null) {
            throw new InvalidAttribute(sprintf(
                '#[Inject] found on property %s::%s but unable to guess what to inject, please add a type to the property',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            ));
        }

        $definition->addPropertyInjection(
            new PropertyInjection($property->getName(), new Reference($entryName), $classname)
        );
    }

    /**
     * Browse the object's methods looking for annotated methods.
     */
    private function readMethods(ReflectionClass $class, ObjectDefinition $objectDefinition)
    {
        // This will look in all the methods, including those of the parent classes
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()) {
                continue;
            }

            $methodInjection = $this->getMethodInjection($method);

            if (! $methodInjection) {
                continue;
            }

            if ($method->isConstructor()) {
                $objectDefinition->completeConstructorInjection($methodInjection);
            } else {
                $objectDefinition->completeFirstMethodInjection($methodInjection);
            }
        }
    }

    /**
     * @return MethodInjection|null
     */
    private function getMethodInjection(ReflectionMethod $method)
    {
        // Look for #[Inject] attribute
        $attribute = $method->getAttributes(InjectAttribute::class)[0] ?? null;

        if ($attribute) {
            /** @var InjectAttribute $inject */
            $inject = $attribute->newInstance();
            $annotationParameters = $inject->getParameters();
        } else {
            // Look for @Inject annotation
            try {
                $annotation = $this->getAnnotationReader()->getMethodAnnotation($method, 'DI\Annotation\Inject');
            } catch (InvalidAnnotation $e) {
                throw new InvalidAnnotation(sprintf(
                    '@Inject annotation on %s::%s is malformed. %s',
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                    $e->getMessage()
                ), 0, $e);
            }

            // @Inject on constructor is implicit
            if (! ($annotation || $method->isConstructor())) {
                return null;
            }

            $annotationParameters = $annotation instanceof InjectAnnotation ? $annotation->getParameters() : [];
        }

        $parameters = [];
        foreach ($method->getParameters() as $index => $parameter) {
            $entryName = $this->getMethodParameter($index, $parameter, $annotationParameters);

            if ($entryName !== null) {
                $parameters[$index] = new Reference($entryName);
            }
        }

        if ($method->isConstructor()) {
            return MethodInjection::constructor($parameters);
        }

        return new MethodInjection($method->getName(), $parameters);
    }

    /**
     * @param int                 $parameterIndex
     *
     * @return string|null Entry name or null if not found.
     */
    private function getMethodParameter($parameterIndex, ReflectionParameter $parameter, array $annotationParameters)
    {
        // Let's check if this parameter has an #[Inject] attribute
        $attribute = $parameter->getAttributes(InjectAttribute::class)[0] ?? null;
        if ($attribute) {
            /** @var InjectAttribute $inject */
            $inject = $attribute->newInstance();

            return $inject->getName();
        }

        // @Inject has definition for this parameter (by index, or by name)
        if (isset($annotationParameters[$parameterIndex])) {
            return $annotationParameters[$parameterIndex];
        }
        if (isset($annotationParameters[$parameter->getName()])) {
            return $annotationParameters[$parameter->getName()];
        }

        // Skip optional parameters if not explicitly defined
        if ($parameter->isOptional()) {
            return null;
        }

        // Try to use the type-hinting
        $parameterType = $parameter->getType();
        if ($parameterType && $parameterType instanceof ReflectionNamedType && !$parameterType->isBuiltin()) {
            return $parameterType->getName();
        }

        // Last resort, look for @param tag
        return $this->getPhpDocReader()->getParameterClass($parameter);
    }

    /**
     * @return Reader The annotation reader
     */
    public function getAnnotationReader()
    {
        if ($this->annotationReader === null) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->annotationReader = new SimpleAnnotationReader();
            $this->annotationReader->addNamespace('DI\Annotation');
        }

        return $this->annotationReader;
    }

    /**
     * @return PhpDocReader
     */
    private function getPhpDocReader()
    {
        if ($this->phpDocReader === null) {
            $this->phpDocReader = new PhpDocReader($this->ignorePhpDocErrors);
        }

        return $this->phpDocReader;
    }

    private function readInjectableAnnotation(ReflectionClass $class, ObjectDefinition $definition)
    {
        try {
            /** @var InjectableAnnotation|null $annotation */
            $annotation = $this->getAnnotationReader()
                ->getClassAnnotation($class, 'DI\Annotation\Injectable');
        } catch (UnexpectedValueException $e) {
            throw new InvalidAnnotation(sprintf(
                'Error while reading @Injectable on %s: %s',
                $class->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        if (! $annotation) {
            return;
        }

        if ($annotation->isLazy() !== null) {
            $definition->setLazy($annotation->isLazy());
        }
    }

    /**
     * @throws InvalidAttribute
     */
    private function readInjectableAttribute(ReflectionClass $class, ObjectDefinition $definition) : void
    {
        try {
            $attribute = $class->getAttributes(InjectableAttribute::class)[0] ?? null;
            if (! $attribute) {
                return;
            }
            $attribute = $attribute->newInstance();
        } catch (Throwable $e) {
            throw new InvalidAttribute(sprintf(
                'Error while reading #[Injectable] on %s: %s',
                $class->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        if ($attribute->isLazy() !== null) {
            $definition->setLazy($attribute->isLazy());
        }
    }
}
