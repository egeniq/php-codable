<?php

declare(strict_types=1);

namespace MinVWS\Codable\Reflection\Attributes;

use Attribute;
use MinVWS\Codable\Decoding\StaticPropertyDecoder;
use MinVWS\Codable\Encoding\StaticPropertyEncoder;

#[Attribute]
readonly class CodableCoder
{
    public array $args;

    /**
     * @param class-string<StaticPropertyDecoder|StaticPropertyEncoder> $class
     */
    public function __construct(public string $class, ...$args) {
        $this->args = $args;
    }
}
