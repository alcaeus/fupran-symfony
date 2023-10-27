<?php

namespace MongoDB\Bundle\Codec;

use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;
use ReflectionEnum;
use ReflectionException;
use TypeError;
use ValueError;

use function call_user_func;
use function is_int;
use function is_string;
use function sprintf;

/**
 * @template-implements Codec<string|int, mixed>
 */
class EnumCodec implements Codec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    private readonly ReflectionEnum $reflection;

    public function __construct(
        private readonly string $enumClass,
    ) {
        $this->reflection = new ReflectionEnum($enumClass);
    }

    public function canDecode($value): bool
    {
        return is_int($value) || is_string($value);
    }

    public function canEncode($value): bool
    {
        return $value instanceof $this->enumClass;
    }

    public function decode($value)
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        try {
            if ($this->reflection->isBacked()) {
                return call_user_func([$this->enumClass, 'from'], $value);
            }

            return $this->reflection->getCase($value)->getValue();
        } catch (TypeError|ReflectionException $e) {
            throw new ValueError(
                sprintf('"%s" is not a valid value for enum "%s"', $value, $this->enumClass),
                0,
                $e,
            );
        }
    }

    public function encode($value)
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return $value->value ?? $value->name;
    }
}
