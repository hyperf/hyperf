<?php

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