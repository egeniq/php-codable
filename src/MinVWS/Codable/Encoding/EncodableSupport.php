<?php

declare(strict_types=1);

namespace MinVWS\Codable\Encoding;

use DateTimeInterface;
use MinVWS\Codable\Decoding\StaticPropertyDecoder;
use MinVWS\Codable\Exceptions\CodableException;
use MinVWS\Codable\Reflection\Attributes\CodableCallbacks;
use MinVWS\Codable\Reflection\Attributes\CodableCoder;
use MinVWS\Codable\Reflection\Attributes\CodableDateTime;
use MinVWS\Codable\Reflection\Attributes\CodableIgnore;
use MinVWS\Codable\Reflection\Attributes\CodableModes;
use MinVWS\Codable\Reflection\Attributes\CodableName;
use MinVWS\Codable\Reflection\CodableIgnoreType;
use MinVWS\Codable\Reflection\ReflectionCodableClass;
use MinVWS\Codable\Reflection\ReflectionCodableProperty;
use ReflectionNamedType;

trait EncodableSupport
{
    protected function getValueForProperty(ReflectionCodableProperty $property): mixed
    {
        return $property->getReflectionProperty()->getValue($this);
    }

    protected function shouldEncodeProperty(ReflectionCodableProperty $property, EncodingContainer $container): bool
    {
        $ignore = $property->getAttribute(CodableIgnore::class)?->type;
        if ($ignore === CodableIgnoreType::Always || $ignore === CodableIgnoreType::OnEncode) {
            return false;
        }

        $encodingModes = $property->getAttribute(CodableModes::class)?->encodingModes;
        if ($encodingModes !== null && !in_array($container->getContext()->getMode(), $encodingModes)) {
            return false;
        }

        return true;
    }

    /**
     * @throws CodableException
     */
    protected function encodeProperty(ReflectionCodableProperty $property, EncodingContainer $container): void
    {
        if (!$this->shouldEncodeProperty($property, $container)) {
            return;
        }

        $callback = $property->getAttribute(CodableCallbacks::class)?->encode;
        if (isset($callback) && is_callable($callback)) {
            call_user_func($callback, $container);
            return;
        }

        $type = $property->getType(ReflectionNamedType::class);

        $name = $property->getAttribute(CodableName::class)?->name ?? $property->getName();
        $propertyContainer = $container->nestedContainer($name);

        if ($property->hasAttribute(CodableCoder::class)) {
            $attr = $property->getAttribute(CodableCoder::class);
            $class = $attr->class;
            if (is_a($class, StaticPropertyEncoder::class, true)) {
                $class::encodeProperty($property, $propertyContainer, $attr->args);
                return;
            }
        }

        $value = $this->getValueForProperty($property);

        if (!$type->isBuiltin() && is_a($type->getName(), DateTimeInterface::class, true)) {
            assert($value === null || $value instanceof DateTimeInterface);
            $attr = $property->getAttribute(CodableDateTime::class);
            $propertyContainer->encodeDateTime($value, $attr?->format, $attr?->timeZone);
        } else {
            $propertyContainer->encode($value);
        }
    }

    /**
     * @throws CodableException
     */
    public function encode(EncodingContainer $container): void
    {
        $class = ReflectionCodableClass::forClass(static::class);
        foreach ($class->getProperties() as $property) {
            $this->encodeProperty($property, $container);
        }
    }
}
