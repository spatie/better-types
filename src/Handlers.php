<?php

namespace Spatie\BetterTypes;

use ReflectionClass;

class Handlers
{
    /** @var \Spatie\BetterTypes\Method[] */
    private array $methods = [];

    public function __construct(
        private ReflectionClass $class
    ) {
        foreach ($class->getMethods() as $reflectionMethod) {
            $this->methods[$reflectionMethod->getName()] = new Method($reflectionMethod);
        }
    }

    public function find(mixed ...$input): array
    {
        $viableMethods = [];

        foreach ($this->methods as $name => $method) {
            if ($method->accepts(...$input)) {
                $viableMethods[] = $name;
            }
        }

        return $viableMethods;
    }
}
