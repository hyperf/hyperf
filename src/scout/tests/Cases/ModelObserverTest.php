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

use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Restored;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Model;
use Hyperf\Scout\ModelObserver;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ModelObserverTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        $this->assertTrue(true);
    }

    public function testSavedHandlerMakesModelSearchable()
    {
        $observer = new ModelObserver();
        $model = m::mock(Model::class);
        $model->shouldReceive('shouldBeSearchable')->andReturn(true);
        $model->shouldReceive('searchable');

        $observer->saved(new Saved($model));
    }

    public function testSavedHandlerDoesntMakeModelSearchableWhenDisabled()
    {
        $observer = new ModelObserver();
        $model = m::mock(Model::class);
        $observer->disableSyncingFor(get_class($model));
        $model->shouldReceive('searchable')->never();
        $observer->saved(new Saved($model));
    }

    public function testSavedHandlerMakesModelUnsearchableWhenDisabledPerModelRule()
    {
        $observer = new ModelObserver();
        $model = m::mock(Model::class);
        $model->shouldReceive('shouldBeSearchable')->andReturn(false);
        $model->shouldReceive('searchable')->never();
        $model->shouldReceive('unsearchable');
        $observer->saved(new Saved($model));
    }

    public function testDeletedHandlerMakesModelUnsearchable()
    {
        $observer = new ModelObserver();
        $model = m::mock(Model::class);
        $model->shouldReceive('unsearchable');
        $observer->deleted(new Deleted($model));
    }

    public function testRestoredHandlerMakesModelSearchable()
    {
        $observer = new ModelObserver();
        $model = m::mock(Model::class);
        $model->shouldReceive('shouldBeSearchable')->andReturn(true);
        $model->shouldReceive('searchable');
        $observer->restored(new Restored($model));
    }
}
