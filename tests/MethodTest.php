<?php

namespace Spatie\BetterTypes\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\BetterTypes\Method;

class MethodTest extends TestCase
{
    private int $integer = 1;

    private string $string = 'string';

    private object $object;

    private $null = null;

    private Bar $bar;

    public function trueValues(): Generator
    {
        $this->object = (object) [];
        $this->bar = new Bar();

        yield ['', []];
        yield ['int $a, string|int $b', [$this->integer, $this->integer]];
        yield ['int $a, string|int $b', [$this->integer, $this->string]];

        yield ['?int $a, null|string $b', [$this->null, $this->null]];
        yield ['?int $a, null|string $b', [$this->null, $this->string]];
        yield ['?int $a, null|string $b', [$this->integer, $this->null]];

        // Named arguments
        yield ['int $a, string|int $b', ['b' => $this->string, 'a' => $this->integer]];

        yield ['\Spatie\BetterTypes\Tests\Bar $a, Spatie\BetterTypes\Tests\BarInterface $b', [$this->bar, $this->bar]];
    }

    public function falseValues(): Generator
    {
        $this->object = (object) [];
        $this->bar = new Bar();

        // Strictly prevent missing values
        yield ['', [$this->string]];
        yield ['?int $a, ?int $b', [$this->null]];

        yield ['?int $a', [$this->string]];
        yield ['\Spatie\BetterTypes\Tests\Bar $a', [$this->object]];
    }

    /**
     * @test
     * @dataProvider trueValues
     */
    public function test_true($definition, array $arguments)
    {
        $this->assertTrue($this->makeMethod($definition)->accepts(...$arguments));
    }

    /**
     * @test
     * @dataProvider falseValues
     */
    public function test_false($definition, array $arguments)
    {
        $this->assertFalse($this->makeMethod($definition)->accepts(...$arguments));
    }

    private function makeMethod(string $definition): Method
    {
        eval(<<<PHP
        \$class = new class() {
            public function test($definition) {}
        };
        PHP);

        $method = (new ReflectionClass($class))->getMethod('test');

        return new Method($method);
    }
}

interface BarInterface
{
}

abstract class BarTopParent
{
}

abstract class BarParent extends BarTopParent
{
}

class Bar extends BarParent implements BarInterface
{
}
