<?php

namespace DI\Test\Migration;

use DI\Container;
use DI\Definition\Exception\InvalidAttribute;
use DI\Definition\Exception\InvalidDefinition;
use DI\MigrationContainerBuilder;
use PHPUnit\Framework\TestCase;

/**
 * PHP-DI migration tests.
 */
class MigrationTest extends TestCase
{
    private const FOO = 'Foo';
    private const BAR = 'Bar';

    private const DEFINITIONS = [
        self::FOO => self::FOO,
        self::BAR => self::BAR,
    ];

    /** @dataProvider dataProvider */
    public function testAnnotatedTypedProperty(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AnnotatedTypedProperty::class);

        if ($annotations) {
            $this->assertInstanceOf(Service::class, $object->service);
        } else {
            $this->assertFalse(isset($object->service));
        }
    }

    /** @dataProvider dataProvider */
    public function testAttributedTypedProperty(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AttributedTypedProperty::class);

        if ($attributes) {
            $this->assertInstanceOf(Service::class, $object->service);
        } else {
            $this->assertFalse(isset($object->service));
        }
    }

    /** @dataProvider dataProvider */
    public function testAnnotatedUntypedProperty(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AnnotatedUntypedProperty::class);

        if ($annotations) {
            $this->assertInstanceOf(Service::class, $object->service);
        } else {
            $this->assertNull($object->service);
        }
    }

    /** @dataProvider dataProvider */
    public function testAttributedUntypedProperty(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        if ($attributes) {
            $this->expectException(InvalidAttribute::class);
        }

        $object = $container->get(AttributedUntypedProperty::class);

        $this->assertNull($object->service);
    }

    /** @dataProvider dataProvider */
    public function testAnnotatedNamedProperty(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AnnotatedNamedProperty::class);

        if ($annotations) {
            $this->assertEquals(self::FOO, $object->foo);
        } else {
            $this->assertNull($object->foo);
        }
    }

    /** @dataProvider dataProvider */
    public function testAttributedNamedProperty(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AttributedNamedProperty::class);

        if ($attributes) {
            $this->assertEquals(self::FOO, $object->foo);
        } else {
            $this->assertNull($object->foo);
        }
    }

    /** @dataProvider dataProvider */
    public function testTypedConstructor(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(TypedConstructor::class);

        $this->assertInstanceOf(Service::class, $object->service);
    }

    /** @dataProvider dataProvider */
    public function testUntypedConstructor(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        if (!$annotations) {
            $this->expectException(InvalidDefinition::class);
        }

        $object = $container->get(UntypedConstructor::class);

        $this->assertInstanceOf(Service::class, $object->service);
    }

    /** @dataProvider dataProvider */
    public function testAnnotatedPositionalConstructor(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        if (!$annotations) {
            $this->expectException(InvalidDefinition::class);
        }

        $object = $container->get(AnnotatedPositionalConstructor::class);

        $this->assertEquals(self::FOO, $object->foo);
        $this->assertEquals(self::BAR, $object->bar);
    }

    /** @dataProvider dataProvider */
    public function testAttributedPositionalConstructor(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        if (!$attributes) {
            $this->expectException(InvalidDefinition::class);
        }

        $object = $container->get(AttributedPositionalConstructor::class);

        $this->assertEquals(self::FOO, $object->foo);
        $this->assertEquals(self::BAR, $object->bar);
    }

    /** @dataProvider dataProvider */
    public function testAnnotatedNamedConstructor(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        if (!$annotations) {
            $this->expectException(InvalidDefinition::class);
        }

        $object = $container->get(AnnotatedNamedConstructor::class);

        $this->assertEquals(self::FOO, $object->foo);
        $this->assertEquals(self::BAR, $object->bar);
    }

    /** @dataProvider dataProvider */
    public function testAttributedNamedConstructor(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        if (!$attributes) {
            $this->expectException(InvalidDefinition::class);
        }

        $object = $container->get(AttributedNamedConstructor::class);

        $this->assertEquals(self::FOO, $object->foo);
        $this->assertEquals(self::BAR, $object->bar);
    }

    /** @dataProvider dataProvider */
    public function testAttributedConstructorNamedProperty(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        if (!$attributes) {
            $this->expectException(InvalidDefinition::class);
        }

        $object = $container->get(AttributedConstructorNamedProperty::class);

        $this->assertEquals(self::FOO, $object->foo);
    }

    /** @dataProvider dataProvider */
    public function testAnnotatedTypedMethod(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AnnotatedTypedMethod::class);

        if ($annotations) {
            $this->assertInstanceOf(Service::class, $object->service);
        } else {
            $this->assertNull($object->service);
        }
    }

    /** @dataProvider dataProvider */
    public function testAttributedTypedMethod(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AttributedTypedMethod::class);

        if ($attributes) {
            $this->assertInstanceOf(Service::class, $object->service);
        } else {
            $this->assertNull($object->service);
        }
    }

    /** @dataProvider dataProvider */
    public function testAnnotatedUntypedMethod(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AnnotatedUntypedMethod::class);

        if ($annotations) {
            $this->assertInstanceOf(Service::class, $object->service);
        } else {
            $this->assertNull($object->service);
        }
    }
    /** @dataProvider dataProvider */
    public function testAttributedUntypedMethod(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        if (!$annotations) {
            $this->expectException(InvalidDefinition::class);
        }

        $object = $container->get(AttributedUntypedMethod::class);

        if ($attributes) {
            $this->assertInstanceOf(Service::class, $object->service);
        } else {
            $this->assertNull($object->service);
        }
    }

    /** @dataProvider dataProvider */
    public function testAnnotatedPositionalMethod(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AnnotatedPositionalMethod::class);

        if ($annotations) {
            $this->assertEquals(self::FOO, $object->foo);
            $this->assertEquals(self::BAR, $object->bar);
        } else {
            $this->assertNull($object->foo);
            $this->assertNull($object->bar);
        }
    }

    /** @dataProvider dataProvider */
    public function testAttributedPositionalMethod(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AttributedPositionalMethod::class);

        if ($attributes) {
            $this->assertEquals(self::FOO, $object->foo);
            $this->assertEquals(self::BAR, $object->bar);
        } else {
            $this->assertNull($object->foo);
            $this->assertNull($object->bar);
        }
    }

    /** @dataProvider dataProvider */
    public function testAnnotatedNamedMethod(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AnnotatedNamedMethod::class);

        if ($annotations) {
            $this->assertEquals(self::FOO, $object->foo);
            $this->assertEquals(self::BAR, $object->bar);
        } else {
            $this->assertNull($object->foo);
            $this->assertNull($object->bar);
        }
    }

    /** @dataProvider dataProvider */
    public function testAttributedNamedMethod(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AttributedNamedMethod::class);

        if ($attributes) {
            $this->assertEquals(self::FOO, $object->foo);
            $this->assertEquals(self::BAR, $object->bar);
        } else {
            $this->assertNull($object->foo);
            $this->assertNull($object->bar);
        }
    }

    /** @dataProvider dataProvider */
    public function testAttributedMethodNamedProperty(
        bool $annotations,
        bool $attributes,
        Container $container,
    ): void {
        $object = $container->get(AttributedMethodNamedProperty::class);

        if ($attributes) {
            $this->assertEquals(self::FOO, $object->foo);
        } else {
            $this->assertNull($object->foo);
        }
    }

    public function dataProvider()
    {
        return [
            [true, false, (new MigrationContainerBuilder())
                ->useAnnotations(true)
                ->addDefinitions(self::DEFINITIONS)
                ->build()],
            [true, true, (new MigrationContainerBuilder())
                ->useAnnotations(true)
                ->useAttributes(true)
                ->addDefinitions(self::DEFINITIONS)
                ->build()],
            [false, true, (new MigrationContainerBuilder())
                ->useAttributes(true)
                ->addDefinitions(self::DEFINITIONS)
                ->build()],
        ];
    }
}