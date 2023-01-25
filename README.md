# Better types

Check whether a reflection type or method accepts a given input

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/better-types.svg?style=flat-square)](https://packagist.org/packages/spatie/better-types)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/better-types/run-tests?label=tests)](https://github.com/spatie/better-types/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/better-types/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/better-types/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/better-types.svg?style=flat-square)](https://packagist.org/packages/spatie/better-types)

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/better-types.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/better-types)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/better-types
```

## Usage

Using the `Type` class directly:

```php
function (FooInterface $foo) {}

$reflectionType = …

$type = new Type($reflectionType);

$type->accepts(new Foo()); // true
$type->accepts('invalid string'); // false
```

Using the `Method` class:

```php
function (?FooInterface $foo, ?BarInterface $bar) {}

$reflectionMethod = …

$method = new Method($reflectionMethod);

$method->accepts(new Foo(), new Bar()); // true
$method->accepts(bar: new Bar() foo: new Foo()); // true
$method->accepts(null, new Bar()); // true
$method->accepts(null, null); // true

$method->accepts('string', 1); // false
$method->accepts(new Foo()); // false, you can't omit values
```

Using `Handlers` to determine which methods accept a given set of input:

```php
class Foo
{
    public function acceptsString(string $a) {}
    
    public function acceptsStringToo(string $a) {}
    
    public function acceptsInt(int $a) {}
}

$reflectionClass = …

$handlers = new Handlers($reflectionClass);

$handlers->accepts('string')->all(); // ['acceptsString', 'acceptsStringToo']
$handlers->accepts(1)->first(); // 'acceptsInt'
```

Using the `Attributes` class to find and instantiate attributes with a fluent API:

```php
Attributes::new(AttributesTestClass::class)
    ->instanceOf(AttributesTestAttribute::class)
    ->first();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Brent Roose](https://github.com/spatie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
