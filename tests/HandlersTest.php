<?php

namespace Spatie\BetterTypes\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\BetterTypes\Handlers;
use Spatie\BetterTypes\Method;

class HandlersTest extends TestCase
{
    /** @test */
    public function test_find()
    {
        $reflectionClass = new ReflectionClass(new Baz());

        $handlers = new Handlers($reflectionClass);

        $this->assertEquals(['acceptsString', 'acceptsStringToo'], $handlers->find('string'));
        $this->assertEquals(['acceptsStringToo'], $handlers->find(...['b' => 'string']));
        $this->assertEquals(['acceptsInt'], $handlers->find(1));
        $this->assertEquals([], $handlers->find(new class() {}));
    }

    /** @test */
    public function test_first()
    {
        $reflectionClass = new ReflectionClass(new Baz());

        $handlers = new Handlers($reflectionClass);

        $this->assertEquals('acceptsString', $handlers->first('string'));
        $this->assertEquals('acceptsStringToo', $handlers->first(...['b' => 'string']));
        $this->assertEquals(null, $handlers->first(new class() {}));
    }

    /** @test */
    public function test_visibility()
    {
        $this->assertNull(Handlers::new(Baz::class)->public()->first([]));
        $this->assertNull(Handlers::new(Baz::class)->protected()->first([]));
        $this->assertNotNull(Handlers::new(Baz::class)->private()->first([]));
        $this->assertNotNull(Handlers::new(Baz::class)->public()->protected()->private()->first([]));
    }

    /** @test */
    public function test_filter()
    {
        $this->assertNull(
            Handlers::new(Baz::class)
                ->filter(fn (Method $method) => ! $method->isFinal())
                ->first(0.1)
            );

        $this->assertNotNull(
            Handlers::new(Baz::class)
                ->filter(fn (Method $method) => $method->isFinal())
                ->first(0.1)
            );
    }

    /** @test */
    public function test_reject()
    {
        $this->assertNull(
            Handlers::new(Baz::class)
                ->reject(fn (Method $method) => $method->isFinal())
                ->first(0.1)
            );

        $this->assertNotNull(
            Handlers::new(Baz::class)
                ->reject(fn (Method $method) => ! $method->isFinal())
                ->first(0.1)
            );
    }
}

class Baz
{
    public function acceptsString(string $a)
    {
    }

    public function acceptsStringToo(string $b)
    {
    }

    public function acceptsInt(int $a)
    {
    }

    private function invisible(array $input) {}

    final protected function finalFunction(float $float) {}
}
