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

use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Hyperf\Engine\Channel;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class ModelLoadSumTest extends TestCase
{
    protected function setUp(): void
    {
        $this->channel = new Channel(999);
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));
        $connectionResolverInterface = $container->get(ConnectionResolverInterface::class);
        Register::setConnectionResolver($connectionResolverInterface);
        Schema::create('base_models', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('related1s', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('base_model_id');
            $table->integer('number');
        });

        Schema::create('related2s', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('base_model_id');
            $table->integer('number');
        });

        BaseModel::create();

        Related1::create(['base_model_id' => 1, 'number' => 10]);
        Related1::create(['base_model_id' => 1, 'number' => 11]);
        Related2::create(['base_model_id' => 1, 'number' => 12]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Schema::dropIfExists('base_models');
        Schema::dropIfExists('related1s');
        Schema::dropIfExists('related2s');
    }

    public function testLoadSumSingleRelation()
    {
        $model = BaseModel::first();
        $model->loadSum('related1', 'number');
        $this->assertEquals(21, $model->related1_sum_number);
    }

    public function testLoadSumMultipleRelations()
    {
        $model = BaseModel::first();

        $model->loadSum(['related1', 'related2'], 'number');
        $this->assertEquals(21, $model->related1_sum_number);
        $this->assertEquals(12, $model->related2_sum_number);
    }

    protected function getContainer()
    {
        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')->with(Mockery::any())->andReturnUsing(function ($event) {
            $this->channel->push($event);
        });
        $container = ContainerStub::getContainer(function ($conn) use ($dispatcher) {
            $conn->setEventDispatcher($dispatcher);
        });
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($dispatcher);

        return $container;
    }
}

class BaseModel extends Model
{
    public bool $timestamps = false;

    protected array $guarded = [];

    public function related1()
    {
        return $this->hasMany(Related1::class);
    }

    public function related2()
    {
        return $this->hasMany(Related2::class);
    }
}

class Related1 extends Model
{
    public bool $timestamps = false;

    protected array $fillable = ['base_model_id', 'number'];

    public function parent()
    {
        return $this->belongsTo(BaseModel::class);
    }
}

class Related2 extends Model
{
    public bool $timestamps = false;

    protected array $fillable = ['base_model_id', 'number'];

    public function parent()
    {
        return $this->belongsTo(BaseModel::class);
    }
}
