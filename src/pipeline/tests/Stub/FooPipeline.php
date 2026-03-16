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

namespace HyperfTest\Pipeline\Stub;

use Hyperf\Pipeline\Pipeline;

class FooPipeline extends Pipeline
{
    protected function handleCarry($carry)
    {
        $carry = parent::handleCarry($carry);
        if (is_int($carry)) {
            $carry += 2;
        }
        return $carry;
    }
}
