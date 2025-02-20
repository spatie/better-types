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

    public static function falseValues(): Generator
    {
        yield ['string|int', []];
        yield ['string|int', null];
        yield ['int', 'invalid'];
        yield ['int', 2.1];
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

    private function makeType(string $definition): Type
    {
        eval(<<<PHP
        \$class = new class() {
            public function test($definition \$input) {}
        };
        PHP);

        /** @phpstan-ignore-next-line */
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
