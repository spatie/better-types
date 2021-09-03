<?php

namespace Spatie\BetterTypes;

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
        public ReflectionMethod $reflectionMethod
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

    public function visibility(): string
    {
        $modifiers = $this->reflectionMethod->getModifiers();

        return match(true) {
            ($modifiers & ReflectionMethod::IS_PRIVATE) !== 0 => self::PRIVATE,
            ($modifiers & ReflectionMethod::IS_PROTECTED) !== 0 => self::PROTECTED,
            default => self::PUBLIC,
        };
    }

    public function isStatic(): bool
    {
        return $this->reflectionMethod->getModifiers() & ReflectionMethod::IS_STATIC;
    }

    public function isFinal(): bool
    {
        return $this->reflectionMethod->getModifiers() & ReflectionMethod::IS_FINAL;
    }

    public function isAbstract(): bool
    {
        return $this->reflectionMethod->getModifiers() & ReflectionMethod::IS_ABSTRACT;
    }

    public function isPublic(): bool
    {
        return $this->reflectionMethod->getModifiers() & ReflectionMethod::IS_PUBLIC;
    }

    public function isProtected(): bool
    {
        return $this->reflectionMethod->getModifiers() & ReflectionMethod::IS_PROTECTED;
    }

    public function isPrivate(): bool
    {
        return $this->reflectionMethod->getModifiers() & ReflectionMethod::IS_PRIVATE;
    }
}
