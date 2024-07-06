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
use HyperfTest\Scout\Stub\ContainerStub;
use HyperfTest\Scout\Stub\ModelStubForMakeAllSearchable;
use HyperfTest\Scout\Stub\SearchableModel;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class SearchableTest extends TestCase
{
    protected function setUp(): void
    {
        ContainerStub::mockContainer();
    }

    protected function tearDown(): void
    {
        m::close();
        $this->assertTrue(true);
        ContainerStub::unsetContainer();
    }

    public function testSearchableUsingUpdateIsCalledOnCollection()
    {
        $collection = m::mock(Collection::class);
        $collection->shouldReceive('isEmpty')->andReturn(false);
        $collection->shouldReceive('first->searchableUsing->update')->with($collection);
        $model = new SearchableModel();
        $model->queueMakeSearchable($collection);
    }

    public function testSearchableUsingUpdateIsNotCalledOnEmptyCollection()
    {
        $collection = m::mock(Collection::class);
        $collection->shouldReceive('isEmpty')->andReturn(true);
        $collection->shouldNotReceive('first->searchableUsing->update');
        $model = new SearchableModel();
        $model->queueMakeSearchable($collection);
    }

    public function testSearchableUsingDeleteIsCalledOnCollection()
    {
        $collection = m::mock(Collection::class);
        $collection->shouldReceive('isEmpty')->andReturn(false);
        $collection->shouldReceive('first->searchableUsing->delete')->with($collection);
        $model = new SearchableModel();
        $model->queueRemoveFromSearch($collection);
    }

    public function testSearchableUsingDeleteIsNotCalledOnEmptyCollection()
    {
        $collection = m::mock(Collection::class);
        $collection->shouldReceive('isEmpty')->andReturn(true);
        $collection->shouldNotReceive('first->searchableUsing->delete');
        $model = new SearchableModel();
        $model->queueRemoveFromSearch($collection);
    }

    public function testMakeAllSearchableUsesOrderBy()
    {
        ModelStubForMakeAllSearchable::makeAllSearchable();
    }
}

namespace Hyperf\Scout;

function config($arg)
{
    return false;
}
