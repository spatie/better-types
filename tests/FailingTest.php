<?php

namespace Spatie\BetterTypes\Tests;

use Generator;
use ReflectionClass;
use Spatie\BetterTypes\Method;
use PHPUnit\Framework\TestCase;
use Spatie\BetterTypes\Handlers;

class FailingTest extends TestCase
{
    public function test_that_looking_up_handlers_by_interface_fails()
    {
        $GLOBALS['better-types.accepts-interfaces'] = false;
        $target_class = new class {
            public function thisMethodShouldAcceptClass(AnExcellentInterface $a)
            {}
        };

        $handlers = Handlers::new($target_class)
            ->public()
            ->acceptsTypes([AnEnjoyableClass::class])
            ->all();

        $this->assertCount(0, $handlers);
        $this->assertNotContains('thisMethodShouldAcceptClass', $handlers->map->getName());
    }

    public function test_that_looking_up_handlers_by_interface_succeeds()
    {
        $GLOBALS['better-types.accepts-interfaces'] = true;
        $target_class = new class {
            public function thisMethodShouldAcceptClass(AnExcellentInterface $a)
            {}
        };

        $handlers = Handlers::new($target_class)
            ->public()
            ->acceptsTypes([AnEnjoyableClass::class])
            ->all();

        $this->assertCount(1, $handlers);
        $this->assertContains('thisMethodShouldAcceptClass', $handlers->map->getName());
    }
}

interface AnExcellentInterface
{}

class AnEnjoyableClass implements AnExcellentInterface
{}