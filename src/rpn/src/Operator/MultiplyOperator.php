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
namespace Hyperf\Rpn\Operator;

class MultiplyOperator extends Operator
{
    public function getOperator(): string
    {
        return '*';
    }

    public function execute(array $parameters, int $scale, array $bindings = []): string
    {
        $parameters = $this->fromBindings($parameters, $bindings);
        $parameters[] = $scale;
        return bcmul(...$parameters);
    }
}
