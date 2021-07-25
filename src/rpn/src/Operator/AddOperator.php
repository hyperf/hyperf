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

class AddOperator extends Operator
{
    public function getOperator(): string
    {
        return '+';
    }

    public function execute(array $paramaters, int $scale): string
    {
        $paramaters[] = $scale;
        return bcadd(...$paramaters);
    }
}
