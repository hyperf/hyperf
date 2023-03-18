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
namespace HyperfTest\Scout\Cases;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Paginator\Paginator;
use Hyperf\Scout\Builder;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class BuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        $this->assertTrue(true);
    }

    public function testPaginationCorrectlyHandlesPaginatedResults()
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
        $engine->shouldReceive('map')->andReturn($results = Collection::make([new stdClass()]));
        $engine->shouldReceive('getTotalCount')->andReturn(100);
        $model->shouldReceive('newCollection')->andReturn($results);
        $builder->paginate();
    }

    public function testMacroable()
    {
        Builder::macro('foo', function () {
            return 'bar';
        });
        $builder = new Builder($model = m::mock(Model::class), 'zonda');
        $this->assertEquals(
            'bar',
            $builder->foo()
        );
    }

    public function testHardDeleteDoesntSetWheres()
    {
        $builder = new Builder($model = m::mock(Model::class), 'zonda', null, false);
        $this->assertArrayNotHasKey('__soft_deleted', $builder->wheres);
    }

    public function testSoftDeleteSetsWheres()
    {
        $builder = new Builder($model = m::mock(Model::class), 'zonda', null, true);
        $this->assertEquals(0, $builder->wheres['__soft_deleted']);
    }
}
