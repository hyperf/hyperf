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
class DatabaseIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();
        $container->shouldReceive('get')->with('db.connector.mysql')->andReturn(new MySqlConnector());
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

        $db2Config = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'hyperf2',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ];

        $connection = $connector->make($dbConfig, 'default');
        $connection2 = $connector->make($db2Config, 'second_connection');

        $resolver = new ConnectionResolver(['default' => $connection, 'second_connection' => $connection2]);

        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver);
        $container = ContainerStub::getContainer();
        $db = new Db($container);
        $container->shouldReceive('get')->with(Db::class)->andReturn($db);
        Register::setConnectionResolver($resolver);
    }

    protected function tearDown(): void
    {
        foreach (['default', 'second_connection'] as $connection) {
            $this->schema($connection)->drop('users');
        }
    }

    public function testUpdateOrCreateOnDifferentConnection(): void
    {
        $this->createSchema();
        ModelTestUser::create(['email' => 'taylorotwell@gmail.com']);

        ModelTestUser::on('second_connection')->updateOrCreate(
            ['email' => 'taylorotwell@gmail.com'],
            ['name' => 'Taylor Otwell']
        );

        ModelTestUser::on('second_connection')->updateOrCreate(
            ['email' => 'themsaid@gmail.com'],
            ['name' => 'Mohamed Said']
        );

        $this->assertEquals(1, ModelTestUser::count());
        $this->assertEquals(2, ModelTestUser::on('second_connection')->count());
    }

    public function testCheckAndCreateMethodsOnMultiConnections(): void
    {
        $this->createSchema();
        ModelTestUser::create(['id' => 1, 'email' => 'taylorotwell@gmail.com']);
        ModelTestUser::on('second_connection')->find(
            ModelTestUser::on('second_connection')->insert(['id' => 2, 'email' => 'themsaid@gmail.com'])
        );

        $user1 = ModelTestUser::on('second_connection')->findOrNew(1);
        $user2 = ModelTestUser::on('second_connection')->findOrNew(2);
        $this->assertFalse($user1->exists);
        $this->assertTrue($user2->exists);
        $this->assertSame('second_connection', $user1->getConnectionName());
        $this->assertSame('second_connection', $user2->getConnectionName());

        $user1 = ModelTestUser::on('second_connection')->firstOrNew(['email' => 'taylorotwell@gmail.com']);
        $user2 = ModelTestUser::on('second_connection')->firstOrNew(['email' => 'themsaid@gmail.com']);
        $this->assertFalse($user1->exists);
        $this->assertTrue($user2->exists);
        $this->assertSame('second_connection', $user1->getConnectionName());
        $this->assertSame('second_connection', $user2->getConnectionName());

        $this->assertEquals(1, ModelTestUser::on('second_connection')->count());
        $user1 = ModelTestUser::on('second_connection')->firstOrCreate(['email' => 'taylorotwell@gmail.com']);
        $user2 = ModelTestUser::on('second_connection')->firstOrCreate(['email' => 'themsaid@gmail.com']);
        $this->assertSame('second_connection', $user1->getConnectionName());
        $this->assertSame('second_connection', $user2->getConnectionName());
        $this->assertEquals(2, ModelTestUser::on('second_connection')->count());
    }

    protected function createSchema(): void
    {
        foreach (['default', 'second_connection'] as $connection) {
            $this->schema($connection)->create('users', function ($table) {
                $table->increments('id');
                $table->string('email')->nullable();
                $table->string('name')->nullable();
                $table->string('value')->nullable();
                $table->timestamp('birthday')->nullable();
                $table->timestamps();
            });
        }
    }

    protected function connection($connection = 'default'): Connection
    {
        return Register::getConnectionResolver()->connection($connection);
    }

    protected function schema($connection = 'default'): Builder
    {
        return $this->connection($connection)->getSchemaBuilder();
    }
}
class ModelTestUser extends Model
{
    protected ?string $table = 'users';

    protected array $casts = ['birthday' => 'datetime'];

    protected array $guarded = [];
}
