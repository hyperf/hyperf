<?php

namespace Hyperflex\Di\Exception;


use Hyperflex\Di\Definition\DefinitionInterface;

class InvalidDefinitionException extends Exception
{
    public static function create(DefinitionInterface $definition, string $message, \Exception $previous = null): self
    {
        return new self(sprintf('%s' . PHP_EOL . 'Full definition:' . PHP_EOL . '%s', $message, (string)$definition), 0, $previous);
    }
}