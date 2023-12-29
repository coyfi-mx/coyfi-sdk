<?php

namespace Coyfi\Cfdi;

use ReflectionClass;
use ReflectionProperty;

abstract class CoyfiObject
{
    public function __construct($attributes = [])
    {
        $this->fill($attributes);
    }

    public static function create($attributes): static
    {
        $object = new static;
        $object->fill($attributes);
        if (method_exists(static::class, 'save')) {
            $object->save();
        }

        return $object;
    }

    public function fill($attributes): void
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray()
    {
        $attributes = $this->getAttributes();

        return array_combine(
            $attributes,
            array_map(function ($attribute) {
                $attributeValue = $this->$attribute ?? null;
                if ($attributeValue instanceof CoyfiObject) {
                    return $attributeValue->toArray();
                }
                if (is_array($attributeValue)) {
                    return array_map(fn (CoyfiObject $item) => $item->toArray(), $attributeValue);
                }

                return $this->$attribute ?? null;
            }, $attributes)
        );
    }

    private function getAttributes(): array
    {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        return array_map(fn ($property) => $property->name, $properties);
    }
}
