<?php

namespace Spatie\BetterTypes;

use ReflectionNamedType;
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
        private null | ReflectionNamedType | ReflectionUnionType $type
    ) {
        if ($type === null) {
            $this->isNullable = true;
            $this->isMixed = true;
        }

        if ($type instanceof ReflectionNamedType) {
            $this->acceptedTypes = [$this->normalize($type->getName())];
            $this->isNullable = $type->allowsNull();
            $this->isMixed = $type->getName() === 'mixed';
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
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
