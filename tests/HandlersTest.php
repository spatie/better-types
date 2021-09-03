<?php

namespace Spatie\BetterTypes\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\BetterTypes\Handlers;

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
}
