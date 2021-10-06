<?php

namespace Spatie\BetterTypes;

use Closure;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Reflector;

/**
 * @template AttributeType
 */
class Attributes
{
    private ?string $instanceOf = null;

    /** @var Closure[] */
    private array $filters = [];

    private bool $asAttributes = false;

    public static function new(array|string|object $reflection): self
    {
        if (is_array($reflection)) {
            $reflection = new ReflectionMethod(...$reflection);
        }

        if (is_string($reflection) && class_exists($reflection)) {
            $reflection = new ReflectionClass($reflection);
        }

        if (! $reflection instanceof Reflector) {
            $reflection = new ReflectionClass($reflection);
        }

        return new self($reflection);
    }

    public function __construct(
        private ReflectionClass | ReflectionMethod $reflection
    ) {
    }

    /**
     * @template InputAttributeType
     *
     * @param class-string<InputAttributeType> $className
     *
     * @return self<InputAttributeType>
     */
    public function instanceOf(string $className): self
    {
        /** @var self<InputAttributeType> $clone */
        $clone = clone $this;

        $clone->instanceOf = $className;

        return $clone;
    }

    /**
     * @return self<ReflectionAttribute>
     */
    public function asAttributes(): self
    {
        $clone = clone $this;

        $clone->asAttributes = true;

        return $clone;
    }

    /**
     * @param Closure(\ReflectionAttribute): bool $filter
     *
     * @return self
     */
    public function filter(Closure $filter): self
    {
        $clone = clone $this;

        $clone->filters[] = $filter;

        return $clone;
    }

    /**
     * @param Closure(\ReflectionAttribute): bool $reject
     *
     * @return self
     */
    public function reject(Closure $reject): self
    {
        return $this->filter(fn (ReflectionAttribute $attribute) => ! $reject($attribute));
    }

    /**
     * @return iterable<AttributeType>|\Illuminate\Support\Collection
     */
    public function all(): iterable|Collection
    {
        $allAttributes = [];

        if ($this->instanceOf) {
            $attributes = $this->reflection->getAttributes($this->instanceOf, ReflectionAttribute::IS_INSTANCEOF);
        } else {
            $attributes = $this->reflection->getAttributes();
        }

        foreach ($attributes as $attribute) {
            if (! $this->filterAllows($attribute)) {
                continue;
            }

            if (! $this->asAttributes) {
                $attribute = $attribute->newInstance();
            }

            $allAttributes[] = $attribute;
        }

        return collect($allAttributes);
    }

    /**
     * @return AttributeType
     */
    public function first(): mixed
    {
        $first = $this->asAttributes()->all()->first();

        if (! $this->asAttributes) {
            $first = $first->newInstance();
        }

        return $first;
    }

    private function filterAllows(ReflectionAttribute $attribute): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter($attribute) === false) {
                return false;
            }
        }

        return true;
    }
}
