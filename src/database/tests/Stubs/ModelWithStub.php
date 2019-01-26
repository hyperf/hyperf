<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Mockery;

class ModelWithStub extends Model
{
    public function newQuery()
    {
        $mock = Mockery::mock(Builder::class);
        $mock->shouldReceive('with')->once()->with(['foo', 'bar'])->andReturn('foo');

        return $mock;
    }
}
