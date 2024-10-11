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

namespace HyperfTest\Database;

use Hyperf\Database\Exception\ClassMorphViolationException;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Pivot;
use Hyperf\Database\Model\Relations\Relation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseStrictMorphsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Relation::requireMorphMap();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Relation::morphMap([], false);
        Relation::requireMorphMap(false);
    }

    public function testStrictModeThrowsAnExceptionOnClassMap()
    {
        $this->expectException(ClassMorphViolationException::class);

        $model = new TestModel();

        $model->getMorphClass();
    }

    public function testStrictModeDoesNotThrowExceptionWhenMorphMap()
    {
        $model = new TestModel();

        Relation::morphMap([
            'foo' => TestModel::class,
        ]);

        $morphName = $model->getMorphClass();
        $this->assertSame('foo', $morphName);
    }

    public function testMapsCanBeEnforcedInOneMethod()
    {
        $model = new TestModel();

        Relation::requireMorphMap(false);

        Relation::enforceMorphMap([
            'test' => TestModel::class,
        ]);

        $morphName = $model->getMorphClass();
        $this->assertSame('test', $morphName);
    }

    public function testMapIgnoreGenericPivotClass()
    {
        $this->expectNotToPerformAssertions();
        $pivotModel = new Pivot();

        $pivotModel->getMorphClass();
    }

    public function testMapCanBeEnforcedToCustomPivotClass()
    {
        $this->expectException(ClassMorphViolationException::class);

        $pivotModel = new TestPivotModel();

        $pivotModel->getMorphClass();
    }
}

class TestModel extends Model
{
}

class TestPivotModel extends Pivot
{
}
