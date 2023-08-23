<?php

namespace Spatie\BetterTypes;

use Illuminate\Support\Collection;
use ReflectionMethod;

class Method
{
    public const PUBLIC = 'public';
    public const PROTECTED = 'protected';
    public const PRIVATE = 'private';

    /** @var array|Type[] */
    private array $positionalTypes = [];

    /** @var array|Type[] */
    private array $namedTypes = [];

    private int $inputCount;

    public function __construct(
        private ReflectionMethod $reflectionMethod
    ) {
        foreach ($reflectionMethod->getParameters() as $index => $parameter) {
            $type = new Type($parameter->getType());

            $this->positionalTypes[$index] = $type;
            $this->namedTypes[$parameter->getName()] = $type;
        }

        $this->inputCount = count($this->positionalTypes);
    }

    public function accepts(mixed ...$input): bool
    {
        $types = array_is_list($input) ? $this->positionalTypes : $this->namedTypes;

        if (count($input) !== $this->inputCount) {
            return false;
        }

        foreach ($types as $index => $type) {
            $currentInput = $input[$index] ?? null;

            if (! $type->accepts($currentInput)) {
                return false;
            }
        }

        return true;
    }

    public function acceptsTypes(array $input): bool
    {
        if ($GLOBALS['better-types.accepts-interfaces']) {
            return $this->acceptsTypesIncludingInterfaces($input);
        }

        if (count($input) !== $this->inputCount) {
            return false;
        }

        foreach ($this->positionalTypes as $index => $type) {
            if (! $type->hasName($input[$index])) {
                return false;
            }
        }

        return true;
    }

    public function acceptsTypesIncludingInterfaces(array $input): bool
    {
        if (count($input) !== $this->inputCount) {
            return false;
        }

        foreach ($this->positionalTypes as $index => $type) {
            $type_hint = $type->getName();
            $queried_type = $input[$index];

            $type_matches_exactly = $type->hasName($queried_type);

            $type_hint_is_interface = interface_exists($type_hint);
            $queried_type_is_class = class_exists($queried_type);

            $queried_class_implements_hinted_interface = $type_hint_is_interface
                && $queried_type_is_class
                && isset(
                    class_implements(
                        $queried_type
                    )[$type_hint]
                );

            if (! $type_matches_exactly && ! $queried_class_implements_hinted_interface) {
                return false;
            }
        }

        return true;
    }

    public function getName(): string
    {
        return $this->reflectionMethod->getName();
    }

    public function getTypes(): Collection
    {
        return collect($this->namedTypes);
    }

    public function visibility(): string
    {
        return match (true) {
            $this->isPrivate() => self::PRIVATE,
            $this->isProtected() => self::PROTECTED,
            default => self::PUBLIC,
        };
    }

    public function isStatic(): bool
    {
        return ($this->reflectionMethod->getModifiers() & ReflectionMethod::IS_STATIC) !== 0;
    }

    public function isFinal(): bool
    {
        return ($this->reflectionMethod->getModifiers() & ReflectionMethod::IS_FINAL) !== 0;
    }

    public function isAbstract(): bool
    {
        return ($this->reflectionMethod->getModifiers() & ReflectionMethod::IS_ABSTRACT) !== 0;
    }

    public function isPublic(): bool
    {
        return ($this->reflectionMethod->getModifiers() & ReflectionMethod::IS_PUBLIC) !== 0;
    }

    public function isProtected(): bool
    {
        return ($this->reflectionMethod->getModifiers() & ReflectionMethod::IS_PROTECTED) !== 0;
    }

    public function isPrivate(): bool
    {
        return ($this->reflectionMethod->getModifiers() & ReflectionMethod::IS_PRIVATE) !== 0;
    }
}
