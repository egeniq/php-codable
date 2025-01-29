<?php

namespace MinVWS\Codable\Encoding;

use MinVWS\Codable\Exceptions\CodableException;
use MinVWS\Codable\Reflection\ReflectionCodableProperty;

interface StaticPropertyEncoder
{
    /**
     * @throws CodableException
     */
    public static function encodeProperty(ReflectionCodableProperty $property, EncodingContainer $container, array $args): mixed;
}
