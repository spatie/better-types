<?php

namespace Spatie\BetterTypes;

use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class Type
{
    private static $typeMapping = [
        'double' => 'float',
        'int' => 'integer',
        'bool' => 'boolean',
    ];

    private bool $isNullable = false;

    private bool $isMixed = false;

    private array $acceptedTypes = [];

    public function __construct(
        private null | ReflectionType $reflectionType
    ) {
        if ($reflectionType === null) {
            $this->isNullable = true;
            $this->isMixed = true;
        }

        if ($reflectionType instanceof ReflectionNamedType) {
            $this->acceptedTypes = [$this->normalize($reflectionType->getName())];
            $this->isNullable = $reflectionType->allowsNull();
            $this->isMixed = $reflectionType->getName() === 'mixed';
        }

        if ($reflectionType instanceof ReflectionUnionType) {
            foreach ($reflectionType->getTypes() as $namedType) {
                $this->acceptedTypes[] = $this->normalize($namedType->getName());
                $this->isNullable = $this->isNullable || $namedType->allowsNull();
                $this->isMixed = $namedType->getName() === 'mixed';
            }
        }
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
