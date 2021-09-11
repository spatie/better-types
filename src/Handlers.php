<?php

namespace Spatie\BetterTypes;

use Illuminate\Support\Collection;
use ReflectionClass;

class Handlers
{
    /** @var array<string, Method> */
    private array $methods = [];

    /** @var string[] */
    private array $visibilityFilter = [];

    /** @var callable[] */
    private array $filters = [];

    /**
     * @template-covariant T of object
     *
     * @param ReflectionClass<T> $class
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(
        private ReflectionClass $class
    ) {
        foreach ($class->getMethods() as $reflectionMethod) {
            $this->methods[$reflectionMethod->getName()] = new Method($reflectionMethod);
        }
    }

    /**
     * @param object|class-string $object
     */
    public static function new(object | string $object): self
    {
        return new self(
            $object instanceof ReflectionClass
                ? $object
                : new ReflectionClass($object)
        );
    }

    /**
     * @return Collection<string, Method>
     */
    public function all(): Collection
    {
        $allMethods = [];

        foreach ($this->methods as $name => $method) {
            if (! $this->filterAllows($method)) {
                continue;
            }

            $allMethods[$name] = $method;
        }

        return collect($allMethods);
    }

    public function first(): ?Method
    {
        return $this->all()->first();
    }

    /**
     * @param callable(Method): bool $filter
     */
    public function filter(callable $filter): self
    {
        $clone = clone $this;

        $clone->filters[] = $filter;

        return $clone;
    }

    /**
     * @param callable(Method): bool $reject
     */
    public function reject(callable $reject): self
    {
        return $this->filter(fn (Method $method) => ! $reject($method));
    }

    public function accepts(mixed ...$input): self
    {
        return $this->filter(fn (Method $method) => $method->accepts(...$input));
    }

    public function public(): self
    {
        $clone = clone $this;

        $clone->visibilityFilter[] = Method::PUBLIC;

        return $clone;
    }

    public function protected(): self
    {
        $clone = clone $this;

        $clone->visibilityFilter[] = Method::PROTECTED;

        return $clone;
    }

    public function private(): self
    {
        $clone = clone $this;

        $clone->visibilityFilter[] = Method::PRIVATE;

        return $clone;
    }

    private function filterAllows(Method $method): bool
    {
        if (
            $this->visibilityFilter !== []
            && ! in_array($method->visibility(), $this->visibilityFilter, true)
        ) {
            return false;
        }

        foreach ($this->filters as $filter) {
            if ($filter($method) === false) {
                return false;
            }
        }

        return true;
    }
}
