<?php

namespace Spatie\BetterTypes\Tests;

use Generator;
use ReflectionClass;
use Spatie\BetterTypes\Method;
use PHPUnit\Framework\TestCase;
use Spatie\BetterTypes\Handlers;

class FailingTest extends TestCase
{
    public function test_that_a_class_implementing_an_interface_counts_for_acceptsTypes()
    {
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