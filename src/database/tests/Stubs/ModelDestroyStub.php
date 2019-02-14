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

use Mockery;
use stdClass;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Builder;

class ModelDestroyStub extends Model
{
    public function newQuery()
    {
        $mock = Mockery::mock(Builder::class);
        $mock->shouldReceive('whereIn')->once()->with('id', [1, 2, 3])->andReturn($mock);
        $mock->shouldReceive('get')->once()->andReturn([$model = Mockery::mock(stdClass::class)]);
        $model->shouldReceive('delete')->once();

        return $mock;
    }
}
