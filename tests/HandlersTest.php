<?php

namespace Spatie\BetterTypes\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\BetterTypes\Handlers;
use Spatie\BetterTypes\Method;

class HandlersTest extends TestCase
{
    public function test_find(): void
    {
        $reflectionClass = new ReflectionClass(new Baz());

        $handlers = new Handlers($reflectionClass);

        self::assertEquals(['acceptsString', 'acceptsStringToo'], $handlers->accepts('string')->all()->keys()->toArray());
        self::assertEquals(['acceptsStringToo'], $handlers->accepts(...['b' => 'string'])->all()->keys()->toArray());
        self::assertEquals(['acceptsInt'], $handlers->accepts(1)->all()->keys()->toArray());
        self::assertEquals([], $handlers->accepts(new class() {
        })->all()->keys()->toArray());
    }

    public function test_first(): void
    {
        $reflectionClass = new ReflectionClass(new Baz());

        $handlers = new Handlers($reflectionClass);

        self::assertEquals('acceptsString', $handlers->accepts('string')->first()?->getName());
        self::assertEquals('acceptsStringToo', $handlers->accepts(...['b' => 'string'])->first()?->getName());
        self::assertEquals(null, $handlers->accepts(new class() {
        })->first());
    }

    public function test_visibility(): void
    {
        self::assertNull(Handlers::new(Baz::class)->public()->accepts([])->first());
        self::assertNull(Handlers::new(Baz::class)->protected()->accepts([])->first());
        self::assertNotNull(Handlers::new(Baz::class)->private()->accepts([])->first());
        self::assertNotNull(Handlers::new(Baz::class)->public()->protected()->private()->accepts([])->first());
    }

    public function test_all(): void
    {
        self::assertCount(1, Handlers::new(Baz::class)->private()->all());
        self::assertCount(3, Handlers::new(Baz::class)->public()->all());
        self::assertCount(5, Handlers::new(Baz::class)->all());
        self::assertCount(1, Handlers::new(Baz::class)->filter(fn (Method $method) => $method->isFinal())->all());
    }

    public function test_filter(): void
    {
        self::assertNull(
            Handlers::new(Baz::class)
                ->filter(fn (Method $method) => ! $method->isFinal())
                ->accepts(0.1)
                ->first()
        );

        self::assertNotNull(
            Handlers::new(Baz::class)
                ->filter(fn (Method $method) => $method->isFinal())
                ->accepts(0.1)
                ->first()
        );
    }

    public function test_reject(): void
    {
        self::assertNull(
            Handlers::new(Baz::class)
                ->reject(fn (Method $method) => $method->isFinal())
                ->accepts(0.1)
                ->first()
        );

        self::assertNotNull(
            Handlers::new(Baz::class)
                ->reject(fn (Method $method) => ! $method->isFinal())
                ->accepts(0.1)
                ->first()
        );
    }
}

class Baz
{
    public function acceptsString(string $a): void
    {
    }

    public function acceptsStringToo(string $b): void
    {
    }

    public function acceptsInt(int $a): void
    {
    }

    // @phpstan-ignore-next-line
    private function invisible(array $input): void
    {
    }

    final protected function finalFunction(float $float): void
    {
    }
}
