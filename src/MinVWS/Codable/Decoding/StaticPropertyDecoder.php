<?php

namespace MinVWS\Codable\Decoding;

use MinVWS\Codable\Exceptions\CodableException;
use MinVWS\Codable\Reflection\ReflectionCodableProperty;

interface StaticPropertyDecoder
{
    /**
     * @throws CodableException
     */
    public static function decodeProperty(ReflectionCodableProperty $property, DecodingContainer $container, array $args): mixed;
}
