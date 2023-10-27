<?php

namespace MongoDB\Bundle\Metadata\Builder;

use MongoDB\Bundle\Attribute\Field as FieldAttribute;
use MongoDB\Bundle\Metadata\Field;
use MongoDB\Bundle\Metadata\CodecGuesser;
use MongoDB\Bundle\ValueAccessor\MethodAccessor;
use MongoDB\Bundle\ValueAccessor\ReflectionAccessor;
use MongoDB\Bundle\ValueAccessor\ValueGetter;
use MongoDB\Bundle\ValueAccessor\ValueSetter;
use MongoDB\Codec\Codec;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionProperty;

final class FieldBuilder implements MetadataBuilder
{
    public function __construct(private Field $field) {}

    public static function fromReflectionProperty(ReflectionProperty $property): self
    {
        return new self(new Field(
            $property->name,
            null,
            ReflectionAccessor::createGetter($property),
            ReflectionAccessor::createSetter($property),
        ));
    }

    public static function fromReflectionMethod(ReflectionMethod $method): self
    {
        return new self(new Field(
            $method->name,
            null,
            MethodAccessor::createGetter($method),
            null,
        ));
    }

    public static function fromAttribute(
        Reflectionmethod|ReflectionProperty $propertyOrMethod,
        FieldAttribute $attribute,
        ?CodecGuesser $codecGuesser = null,
    ): self {
        $builder = $propertyOrMethod instanceof ReflectionProperty
            ? self::fromReflectionProperty($propertyOrMethod)
            : self::fromReflectionMethod($propertyOrMethod);

        if ($attribute->name !== null) {
            $builder = $builder->withName($attribute->name);
        }

        $codec = $attribute->codec ?? self::guessCodec($codecGuesser, $propertyOrMethod);
        if ($codec) {
            $builder = $builder->withCodec($codec);
        }

        return $builder;
    }

    public function build(): Field
    {
        return $this->field;
    }

    public function withName(string $name): self
    {
        $this->field = new Field(
            $name,
            $this->field->codec,
            $this->field->getter,
            $this->field->setter,
        );

        return $this;
    }

    public function withCodec(?Codec $codec): self
    {
        $this->field = new Field(
            $this->field->name,
            $codec,
            $this->field->getter,
            $this->field->setter,
        );

        return $this;
    }

    public function withGetter(?ValueGetter $getter): self
    {
        $this->field = new Field(
            $this->field->name,
            $this->field->codec,
            $getter,
            $this->field->setter,
        );

        return $this;
    }

    public function withSetter(?ValueSetter $setter): self
    {
        $this->field = new Field(
            $this->field->name,
            $this->field->codec,
            $this->field->getter,
            $setter,
        );

        return $this;
    }

    private static function guessCodec(?CodecGuesser $codecGuesser, ReflectionMethod|ReflectionProperty $propertyOrMethod): ?Codec
    {
        if (! $codecGuesser) {
            return null;
        }

        $type = $propertyOrMethod instanceof ReflectionProperty
            ? $propertyOrMethod->getType()
            : $propertyOrMethod->getReturnType();

        if ($type === null) {
            return null;
        }

        return $codecGuesser->guessCodec($type);
    }
}
