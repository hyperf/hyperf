<?php

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Mockery;
use stdClass;

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