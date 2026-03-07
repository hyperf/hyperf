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

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Schema\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Container;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class DatabaseBelongsToManyEachByIdTest extends TestCase
{
    protected function setUp(): void
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->allows('has')->andReturns(true);
        $container->allows('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->allows('has')->with(EventDispatcherInterface::class)->andReturnFalse();
        $container->allows('get')->with('db.connector.mysql')->andReturns(new MySqlConnector());
        $connector = new ConnectionFactory($container);

        $dbConfig = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'hyperf',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ];

        $connection = $connector->make($dbConfig, 'default');

        $resolver = new ConnectionResolver(['default' => $connection]);

        $container->allows('get')->with(ConnectionResolverInterface::class)->andReturns($resolver);
        $container = ContainerStub::getContainer();
        $db = new Db($container);
        $container->allows('get')->with(Db::class)->andReturns($db);
        Register::setConnectionResolver($resolver);
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->schema()->drop('article_user');
        $this->schema()->drop('articles');
        $this->schema()->drop('users');
    }

    public function createSchema()
    {
        $this->schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('email')->unique();
        });

        $this->schema()->create('articles', function ($table) {
            $table->increments('id');
            $table->string('title');
        });

        $this->schema()->create('article_user', function ($table) {
            $table->increments('id');
            $table->integer('article_id')->unsigned();
            $table->foreign('article_id')->references('id')->on('articles');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function testBelongsToEachById()
    {
        $this->seedData();

        $user = BelongsToManyEachByIdTestTestUser::query()->first();
        $i = 0;
        $user->articles()->eachById(function (BelongsToManyEachByIdTestTestArticle $model) use (&$i) {
            ++$i;
            $this->assertSame($i, $model->id);
            return true;
        }, 100, 'articles.id', 'id');

        $this->assertSame(3, $i);
    }

    protected function connection($connection = 'default'): Connection
    {
        return Register::getConnectionResolver()->connection($connection);
    }

    protected function schema($connection = 'default'): Builder
    {
        return $this->connection($connection)->getSchemaBuilder();
    }

    /**
     * Helpers...
     */
    protected function seedData()
    {
        $user = BelongsToManyEachByIdTestTestUser::create(['id' => 1, 'email' => 'taylorotwell@gmail.com']);
        BelongsToManyEachByIdTestTestArticle::query()->insert([
            ['id' => 1, 'title' => 'Another title'],
            ['id' => 2, 'title' => 'Another title'],
            ['id' => 3, 'title' => 'Another title'],
        ]);

        $user->articles()->sync([1, 2, 3]);
    }
}

class BelongsToManyEachByIdTestTestUser extends Model
{
    public bool $timestamps = false;

    protected ?string $table = 'users';

    protected array $fillable = ['id', 'email'];

    public function articles()
    {
        return $this->belongsToMany(BelongsToManyEachByIdTestTestArticle::class, 'article_user', 'user_id', 'article_id');
    }
}

class BelongsToManyEachByIdTestTestArticle extends Model
{
    public bool $incrementing = false;

    public bool $timestamps = false;

    protected ?string $table = 'articles';

    protected string $keyType = 'string';

    protected array $fillable = ['id', 'title'];
}
