<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Utils\CodeGen;

use Hyperf\Utils\Exception\InvalidArgumentException;
use PhpParser\Node;
use ReflectionParameter;

class PhpParser
{
    public const TYPES = [
        'int',
        'float',
        'string',
        'bool',
        'array',
        'object',
        'resource',
        'mixed',
    ];

    /**
     * @var null|PhpParser
     */
    protected static $instance;

    public static function getInstance(): PhpParser
    {
        if (static::$instance) {
            return static::$instance;
        }
        return static::$instance = new static();
    }

    public function getAstFromReflectionParameter(ReflectionParameter $parameter): Node\Param
    {
        $result = new Node\Param(
            new Node\Expr\Variable($parameter->getName())
        );

        if ($parameter->isDefaultValueAvailable()) {
            $result->default = $this->getExprFromValue($parameter->getDefaultValue());
        }

        if ($parameter->hasType()) {
            $type = $parameter->getType()->getName();
            if (! in_array($type, static::TYPES)) {
                $result->type = new Node\Name('\\' . $type);
            } else {
                $result->type = new Node\Identifier($parameter->getType()->getName());
            }
        }

        if ($parameter->isPassedByReference()) {
            $result->byRef = true;
        }

        if ($parameter->isVariadic()) {
            $result->variadic = true;
        }

        return $result;
    }

    public function getExprFromValue($value): Node\Expr
    {
        switch (gettype($value)) {
            case 'array':
                return new Node\Expr\Array_($value);
            case 'string':
                return new Node\Scalar\String_($value);
            case 'integer':
                return new Node\Scalar\LNumber($value);
            case 'double':
                return new Node\Scalar\DNumber($value);
            case 'NULL':
                return new Node\Expr\ConstFetch(new Node\Name('null'));
            case 'boolean':
                return new Node\Expr\ConstFetch(new Node\Name($value ? 'true' : 'false'));
            default:
                throw new InvalidArgumentException($value . ' is invalid');
        }
    }
}
