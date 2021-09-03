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

        $this->assertEquals(['acceptsString', 'acceptsStringToo'], $handlers->accepts('string')->all()->keys()->toArray());
        $this->assertEquals(['acceptsStringToo'], $handlers->accepts(...['b' => 'string'])->all()->keys()->toArray());
        $this->assertEquals(['acceptsInt'], $handlers->accepts(1)->all()->keys()->toArray());
        $this->assertEquals([], $handlers->accepts(new class() {
        })->all()->keys()->toArray());
    }

    /** @test */
    public function test_first()
    {
        $reflectionClass = new ReflectionClass(new Baz());

        $handlers = new Handlers($reflectionClass);

        $this->assertEquals('acceptsString', $handlers->accepts('string')->first()->getName());
        $this->assertEquals('acceptsStringToo', $handlers->accepts(...['b' => 'string'])->first()->getName());
        $this->assertEquals(null, $handlers->accepts(new class() {
        })->first());
    }

    /** @test */
    public function test_visibility()
    {
        $this->assertNull(Handlers::new(Baz::class)->public()->accepts([])->first());
        $this->assertNull(Handlers::new(Baz::class)->protected()->accepts([])->first());
        $this->assertNotNull(Handlers::new(Baz::class)->private()->accepts([])->first());
        $this->assertNotNull(Handlers::new(Baz::class)->public()->protected()->private()->accepts([])->first());
    }

    /** @test */
    public function test_all()
    {
        $this->assertCount(1, Handlers::new(Baz::class)->private()->all());
        $this->assertCount(3, Handlers::new(Baz::class)->public()->all());
        $this->assertCount(5, Handlers::new(Baz::class)->all());
        $this->assertCount(1, Handlers::new(Baz::class)->filter(fn (Method $method) => $method->isFinal())->all());
    }

    /** @test */
    public function test_filter()
    {
        $this->assertNull(
            Handlers::new(Baz::class)
                ->filter(fn (Method $method) => ! $method->isFinal())
                ->accepts(0.1)
                ->first()
        );

        $this->assertNotNull(
            Handlers::new(Baz::class)
                ->filter(fn (Method $method) => $method->isFinal())
                ->accepts(0.1)
                ->first()
        );
    }

    /** @test */
    public function test_reject()
    {
        $this->assertNull(
            Handlers::new(Baz::class)
                ->reject(fn (Method $method) => $method->isFinal())
                ->accepts(0.1)
                ->first()
        );

        $this->assertNotNull(
            Handlers::new(Baz::class)
                ->reject(fn (Method $method) => ! $method->isFinal())
                ->accepts(0.1)
                ->first()
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

    private function invisible(array $input)
    {
    }

    final protected function finalFunction(float $float)
    {
    }
}
