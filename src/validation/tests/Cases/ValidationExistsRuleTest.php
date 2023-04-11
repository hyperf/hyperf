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
namespace HyperfTest\Validation\Cases;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Schema\Builder;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Server\Entry\EventDispatcher;
use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Validation\DatabasePresenceVerifier;
use Hyperf\Validation\Rules\Exists;
use Hyperf\Validation\Validator;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class ValidationExistsRuleTest extends TestCase
{
    /**
     * Setup the database schema.
     */
    protected function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(true);
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
        $connection = $connector->make($dbConfig);
        $resolver = new ConnectionResolver(['default' => $connection]);
        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver);
        ApplicationContext::setContainer($container);
        Register::setConnectionResolver($resolver);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(new EventDispatcher());

        $this->createSchema();
    }

    /**
     * Tear down the database schema.
     */
    protected function tearDown(): void
    {
        $this->schema('default')->drop('users');

        m::close();
    }

    public function testItCorrectlyFormatsAStringVersionOfTheRule()
    {
        $rule = new Exists('table');
        $rule->where('foo', 'bar');
        $this->assertEquals('exists:table,NULL,foo,"bar"', (string) $rule);

        $rule = new Exists('table', 'column');
        $rule->where('foo', 'bar');
        $this->assertEquals('exists:table,column,foo,"bar"', (string) $rule);
    }

    public function testItChoosesValidRecordsUsingWhereInRule()
    {
        $rule = new Exists('users', 'id');
        $rule->whereIn('type', ['foo', 'bar']);

        DatabaseTestUser::create(['id' => '1', 'type' => 'foo']);
        DatabaseTestUser::create(['id' => '2', 'type' => 'bar']);
        DatabaseTestUser::create(['id' => '3', 'type' => 'baz']);
        DatabaseTestUser::create(['id' => '4', 'type' => 'other']);

        $trans = $this->getIlluminateArrayTranslator();
        $v = new Validator($trans, [], ['id' => $rule]);
        $v->setPresenceVerifier(new DatabasePresenceVerifier(Register::getConnectionResolver()));

        $v->setData(['id' => 1]);
        $this->assertTrue($v->passes());
        $v->setData(['id' => 2]);
        $this->assertTrue($v->passes());
        $v->setData(['id' => 3]);
        $this->assertFalse($v->passes());
        $v->setData(['id' => 4]);
        $this->assertFalse($v->passes());
    }

    public function testItChoosesValidRecordsUsingWhereNotInRule()
    {
        $rule = new Exists('users', 'id');
        $rule->whereNotIn('type', ['foo', 'bar']);

        DatabaseTestUser::create(['id' => '1', 'type' => 'foo']);
        DatabaseTestUser::create(['id' => '2', 'type' => 'bar']);
        DatabaseTestUser::create(['id' => '3', 'type' => 'baz']);
        DatabaseTestUser::create(['id' => '4', 'type' => 'other']);

        $trans = $this->getIlluminateArrayTranslator();
        $v = new Validator($trans, [], ['id' => $rule]);
        $v->setPresenceVerifier(new DatabasePresenceVerifier(Register::getConnectionResolver()));

        $v->setData(['id' => 1]);
        $this->assertFalse($v->passes());
        $v->setData(['id' => 2]);
        $this->assertFalse($v->passes());
        $v->setData(['id' => 3]);
        $this->assertTrue($v->passes());
        $v->setData(['id' => 4]);
        $this->assertTrue($v->passes());
    }

    public function testItChoosesValidRecordsUsingWhereNotInAndWhereNotInRulesTogether()
    {
        $rule = new Exists('users', 'id');
        $rule->whereIn('type', ['foo', 'bar', 'baz'])->whereNotIn('type', ['foo', 'bar']);

        DatabaseTestUser::create(['id' => '1', 'type' => 'foo']);
        DatabaseTestUser::create(['id' => '2', 'type' => 'bar']);
        DatabaseTestUser::create(['id' => '3', 'type' => 'baz']);
        DatabaseTestUser::create(['id' => '4', 'type' => 'other']);

        $trans = $this->getIlluminateArrayTranslator();
        $v = new Validator($trans, [], ['id' => $rule]);
        $v->setPresenceVerifier(new DatabasePresenceVerifier(Register::getConnectionResolver()));

        $v->setData(['id' => 1]);
        $this->assertFalse($v->passes());
        $v->setData(['id' => 2]);
        $this->assertFalse($v->passes());
        $v->setData(['id' => 3]);
        $this->assertTrue($v->passes());
        $v->setData(['id' => 4]);
        $this->assertFalse($v->passes());
    }

    public function getIlluminateArrayTranslator()
    {
        return new Translator(
            new ArrayLoader(),
            'en'
        );
    }

    protected function createSchema()
    {
        $this->schema('default')->create('users', function ($table) {
            $table->unsignedInteger('id');
            $table->string('type');
        });
    }

    /**
     * Get a schema builder instance.
     *
     * @param mixed $connection
     * @return Builder
     */
    protected function schema($connection = 'default')
    {
        return $this->connection($connection)->getSchemaBuilder();
    }

    /**
     * Get a database connection instance.
     *
     * @param mixed $connection
     * @return Connection
     */
    protected function connection($connection = 'default')
    {
        return $this->getConnectionResolver()->connection($connection);
    }

    /**
     * Get connection resolver.
     *
     * @return ConnectionResolverInterface
     */
    protected function getConnectionResolver()
    {
        return Register::getConnectionResolver();
    }
}

/**
 * Database Models.
 */
class DatabaseTestUser extends Model
{
    public bool $timestamps = false;

    protected ?string $table = 'users';

    protected array $guarded = [];
}
