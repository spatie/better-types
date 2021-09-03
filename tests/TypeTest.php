<?php

namespace Spatie\BetterTypes\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\BetterTypes\Type;

class TypeTest extends TestCase
{
    private bool $bool = true;
    private int $integer = 1;
    private float $float = 1.2;
    private string $string = 'string';
    private array $array = [];
    private object $object;
    private $null = null;
    private Foo $foo;

    public function trueValues(): Generator
    {
        $this->object = (object) [];
        $this->foo = new Foo();

        yield ['', $this->bool];
        yield ['mixed', $this->bool];
        yield ['bool', $this->bool];
        yield ['int|bool', $this->bool];

        yield ['', $this->float];
        yield ['mixed', $this->float];
        yield ['float', $this->float];
        yield ['int|float', $this->float];

        yield ['', $this->integer];
        yield ['mixed', $this->integer];
        yield ['int', $this->integer];
        yield ['int|string', $this->integer];

        yield ['', $this->string];
        yield ['mixed', $this->string];
        yield ['string', $this->string];
        yield ['int|string', $this->string];

        yield ['', $this->null];
        yield ['mixed', $this->null];
        yield ['?string', $this->null];
        yield ['string|null', $this->null];

        yield ['', $this->object];
        yield ['mixed', $this->object];
        yield ['object', $this->object];
        yield ['object|string', $this->object];

        yield ['', $this->array];
        yield ['mixed', $this->array];
        yield ['array', $this->array];
        yield ['array|string', $this->array];

        yield ['', $this->foo];
        yield ['mixed', $this->foo];
        yield ['object', $this->foo];
        yield ['object|string', $this->foo];
        yield [FooInterface::class, $this->foo];
        yield [FooParent::class, $this->foo];
        yield [FooTopParent::class, $this->foo];
        yield [Foo::class, $this->foo];
    }

    public function falseValues(): Generator
    {
        yield ['string|int', []];
        yield ['string|int', null];
        yield ['int', 'invalid'];
        yield ['int', 2.1];
    }

    /**
     * @test
     * @dataProvider trueValues
     */
    public function test_true($type, $input)
    {
        if (is_object($input)) {
            $inputAsString = $input::class;
        } elseif (is_array($input)) {
            $inputAsString = 'array';
        } else {
            $inputAsString = (string) $input;
        }

        $this->assertTrue($this->makeType($type)->accepts($input), "{$type} for input {$inputAsString} failed");
    }

    /**
     * @test
     * @dataProvider falseValues
     */
    public function test_false($type, $input)
    {
        $this->assertFalse($this->makeType($type)->accepts($input));
    }

    /** @test */
    public function test_get_name()
    {
        $this->assertEquals('string|int', $this->makeType('string|int')->getName());
        $this->assertEquals('int', $this->makeType('int')->getName());
        $this->assertEquals(Foo::class, $this->makeType(Foo::class)->getName());
    }

    private function makeType(string $definition): ?Type
    {
        eval(<<<PHP
        \$class = new class() {
            public function test($definition \$input) {}
        };
        PHP);

        $parameter = (new ReflectionClass($class))->getMethod('test')->getParameters()[0] ?? null;

        return new Type($parameter?->getType());
    }
}

interface FooInterface
{
}
abstract class FooTopParent
{
}
abstract class FooParent extends FooTopParent
{
}
class Foo extends FooParent implements FooInterface
{
}
