<?php

namespace Spatie\BetterTypes;

use Closure;
use ReflectionClass;

class Handlers
{
    /** @var \Spatie\BetterTypes\Method[] */
    private array $methods = [];

    private array $visibilityFilter = [];

    /** @var Closure[] */
    private array $filters = [];

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

            if ($this->filterRejects($method)) {
                continue;
            }

            if ($method->accepts(...$input)) {
                $viableMethods[] = $name;
            }
        }

        return $viableMethods;
    }

    public function filter(Closure $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function reject(Closure $reject): self
    {
        $this->filters[] = fn (Method $method) => ! $reject($method);

        return $this;
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

    private function filterRejects(Method $method): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter($method) === false) {
                return true;
            }
        }

        return false;
    }
}
