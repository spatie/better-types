<?php

namespace Spatie\BetterTypes;

use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

class Type
{
    private static array $typeMapping = [
        'double' => 'float',
        'int' => 'integer',
        'bool' => 'boolean',
    ];

    private bool $isNullable = false;

    private bool $isMixed = false;

    /**
     * @var array<array-key, null|string>
     */
    private array $acceptedTypes = [];

    private string $name = '';

    public static function new(null|ReflectionType|ReflectionParameter $reflection): self
    {
        if ($reflection instanceof ReflectionParameter) {
            $reflection = $reflection->getType();
        }

        return new self($reflection);
    }

    public function __construct(
        null | ReflectionType $reflectionType
    ) {
        if ($reflectionType === null) {
            $this->isNullable = true;
            $this->isMixed = true;
        }

        if ($reflectionType instanceof ReflectionNamedType) {
            $this->acceptedTypes = [$this->normalize($reflectionType->getName())];
            $this->isNullable = $reflectionType->allowsNull();
            $this->isMixed = $reflectionType->getName() === 'mixed';
            $this->name = $reflectionType->getName();
        }

        if ($reflectionType instanceof ReflectionUnionType) {
            $names = [];

            foreach ($reflectionType->getTypes() as $namedType) {
                $this->acceptedTypes[] = $this->normalize($namedType->getName());
                $this->isNullable = $this->isNullable || $namedType->allowsNull();
                $this->isMixed = $namedType->getName() === 'mixed';
                $names[] = $namedType->getName();
            }

            $this->name = implode('|', $names);
        }
    }

    public function hasName(string $name): bool
    {
        return $this->getName() === $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isUnion(): bool
    {
        return count($this->acceptedTypes) > 1;
    }

    public function unionContains(string $type): bool
    {
        return in_array($this->normalize($type), $this->acceptedTypes, true);
    }

    public function accepts(mixed $input): bool
    {
        if ($this->isMixed) {
            return true;
        }

        if ($this->isNullable && $input === null) {
            return true;
        }

        $inputType = $this->normalize(gettype($input));

        if (in_array($inputType, $this->acceptedTypes)) {
            return true;
        }

        if ($inputType === 'object') {
            $interfaces = class_implements($input);

            $parents = class_parents($input);

            foreach ($this->acceptedTypes as $acceptedType) {
                $extendsOrIs =
                    $input::class === $acceptedType
                    || array_key_exists($acceptedType, $interfaces)
                    || array_key_exists($acceptedType, $parents);

                if ($extendsOrIs) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalize(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        return self::$typeMapping[$type] ?? $type;
    }
}
