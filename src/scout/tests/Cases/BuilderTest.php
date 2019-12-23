<?php


namespace HyperfTest\Scout\Cases;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Paginator\Paginator;
use Hyperf\Scout\Builder;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;

class BuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        $this->assertTrue(true);
    }
    public function test_pagination_correctly_handles_paginated_results()
    {
        Paginator::currentPageResolver(function () {
            return 1;
        });
        Paginator::currentPathResolver(function () {
            return 'http://localhost/foo';
        });
        $builder = new Builder($model = m::mock(Model::class), 'zonda');
        $model->shouldReceive('getPerPage')->andReturn(15);
        $model->shouldReceive('searchableUsing')->andReturn($engine = m::mock());
        $engine->shouldReceive('paginate');
        $engine->shouldReceive('map')->andReturn($results = Collection::make([new stdClass]));
        $engine->shouldReceive('getTotalCount');
        $model->shouldReceive('newCollection')->andReturn($results);
        $builder->paginate();
    }
    public function test_macroable()
    {
        Builder::macro('foo', function () {
            return 'bar';
        });
        $builder = new Builder($model = m::mock(Model::class), 'zonda');
        $this->assertEquals(
            'bar', $builder->foo()
        );
    }
    public function test_hard_delete_doesnt_set_wheres()
    {
        $builder = new Builder($model = m::mock(Model::class), 'zonda', null, false);
        $this->assertArrayNotHasKey('__soft_deleted', $builder->wheres);
    }
    public function test_soft_delete_sets_wheres()
    {
        $builder = new Builder($model = m::mock(Model::class), 'zonda', null, true);
        $this->assertEquals(0, $builder->wheres['__soft_deleted']);
    }
}