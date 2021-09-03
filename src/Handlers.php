<?php

namespace Spatie\BetterTypes;

use ReflectionClass;

class Handlers
{
    /** @var \Spatie\BetterTypes\Method[] */
    private array $methods = [];

    private array $visibilityFilter = [];

    public function __construct(
        private ReflectionClass $class
    ) {
        foreach ($class->getMethods() as $reflectionMethod) {
            $this->methods[$reflectionMethod->getName()] = new Method($reflectionMethod);
        }
    }

    public static function new(object|string $object): self
    {
        return new self(
            $object instanceof ReflectionClass
                ? $object
                : new ReflectionClass($object)
        );
    }

    public function find(mixed ...$input): array
    {
        $viableMethods = [];

        foreach ($this->methods as $name => $method) {
            if (
                $this->visibilityFilter !== []
                && ! in_array($method->visibility(), $this->visibilityFilter)
            ) {
                continue;
            }

            if ($method->accepts(...$input)) {
                $viableMethods[] = $name;
            }
        }

        return $viableMethods;
    }

    public function first(mixed ...$input): ?string
    {
        return $this->find(...$input)[0] ?? null;
    }

    public function public(): self
    {
        $this->visibilityFilter[] = Method::PUBLIC;

        return $this;
    }

    public function protected(): self
    {
        $this->visibilityFilter[] = Method::PROTECTED;

        return $this;
    }

    public function private(): self
    {
        $this->visibilityFilter[] = Method::PRIVATE;

        return $this;
    }
}
