# PHP-DI Migration

[![Software license][ico-license]](LICENSE)
[![PHP version][ico-php]][link-php]
[![Latest release][ico-packagist]][link-packagist]

Eases migration from PHP-DI 6 annotations to PHP-DI 7 attributes.

## Rationale

PHP-DI 7 replaces the usage of PHPDoc `@Inject` and `@Injectable` annotations
with native PHP 8 `#[Inject]` and `#[Injectable]` attributes. This is very much
a change for the better, but all of your code needs to be updated at once,
which can be difficult or dangerous across a large codebase with multiple
contributors. This project provides a smoother migration path by supporting
attributes alongside annotations while you migrate your code.

## Warning

This code has not been extensively tested for all use cases and edge cases.
Use at your own risk!

## Usage

Install the migration package alongside PHP-DI 6:
```sh
composer require zorac/php-di-migration
```
Replace `ContainerBuilder` with `MigrationContainerBuilder` and enable
annotations:
```php
// Before:
$builder = new DI\ContainerBuilder();
$builder->useAnnotations(true);
// etc...
$container = $builder->build();

// After:
$builder = new DI\MigrationContainerBuilder();
$builder->useAnnotations(true);
$builder->useAttributes(true);
// etc...
$container = $builder->build();
```
Commit and merge the changes, and you're ready to start migrating!

Over time, migrate your code from attributes to annotations, remembering to add
strong PHP types anywhere you're currently relying on PHPDoc typing, and to
remove any imports of `DI\Annotation\Inject` etc.

Once your code is all migrated, remove the `useAnnotations(false)` call from
the container builder for final testing. If you missed any typing changes, this
is when things will break.

Once you're happy with your fully migrated code, you can remove
`zorac/php-di-migration` and upgrade to PHP-DI 7, switching back to using
`DI\ContainerBuilder`. You may also be able to remove `doctrine/annotations` if
you're not relying on it for anything else.

Congratulations! Your PHP-DI migration is complete.

## Implementation Notes

* `MigrationContainerBuilder` is a modified version of PHP-DI 6's
  `ContainerBuilder` with changes ported from PHP-DI 7's version to add support
  for `useAttributes`, and additional changes to support annotations and
  attributes in parallel.
* `AttributeAndAnnotationBasedAutowiring` is a modified version of PHP-DI 6's
  `AnnotationBasedAutowiring` with changes ported from PHP-DI 7's
  `AttributeBasedAutowiring` to support annotations and attributes in parallel.
* All other classes are copied verbatim from PHP-DI 7.

[ico-license]: https://img.shields.io/github/license/zorac/php-di-migration.svg?style=flat-square
[ico-php]: https://img.shields.io/packagist/php-v/zorac/php-di-migration.svg?style=flat-square
[ico-packagist]: https://img.shields.io/packagist/v/zorac/php-di-migration.svg?style=flat-square
[link-php]: https://www.php.net/
[link-packagist]: https://packagist.org/packages/zorac/php-di-migration
