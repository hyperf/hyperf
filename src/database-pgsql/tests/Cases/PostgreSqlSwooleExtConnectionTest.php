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
namespace HyperfTest\Database\PgSQL\Cases;

use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\Migrations\DatabaseMigrationRepository;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Schema\Schema;
use Hyperf\Support\Filesystem\Filesystem;
use HyperfTest\Database\PgSQL\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @internal
 * @coversNothing
 */
class PostgreSqlSwooleExtConnectionTest extends TestCase
{
    protected Migrator $migrator;

    public function setUp(): void
    {
        if (SWOOLE_MAJOR_VERSION < 5) {
            $this->markTestSkipped('PostgreSql requires Swoole version >= 5.0.0');
        }

        $resolver = ContainerStub::getContainer()->get(ConnectionResolverInterface::class);

        $this->migrator = new Migrator(
            $repository = new DatabaseMigrationRepository($resolver, 'migrations'),
            $resolver,
            new Filesystem()
        );

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln');

        $this->migrator->setOutput($output);

        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }
    }

    public function tearDown(): void
    {
        Schema::dropIfExists('password_resets_for_pgsql');
        Schema::dropIfExists('migrations');
    }

    public function testSelectMethodDuplicateKeyValueException()
    {
        $connection = ApplicationContext::getContainer()->get(ConnectionResolverInterface::class)->connection();

        $builder = new Builder($connection);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('ERROR:  duplicate key value violates unique constraint "users_email"');

        $id = $builder->from('users')->insertGetId(['email' => 'test@hyperf.io', 'name' => 'hyperf'], 'id');
        $id2 = $builder->from('users')->insertGetId(['email' => 'test@hyperf.io', 'name' => 'hyperf'], 'id');

        // Never here
        $this->assertIsNumeric($id);
        $this->assertIsNumeric($id2);
    }

    public function testThrowExceptionWhenStatementExecutionFails()
    {
        $connection = ApplicationContext::getContainer()->get(ConnectionResolverInterface::class)->connection();

        $builder = new Builder($connection);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('ERROR:  duplicate key value violates unique constraint "users_email"');

        $result = $builder->from('users')->insert(['email' => 'test@hyperf.io', 'name' => 'hyperf']);
        $result2 = $builder->from('users')->insert(['email' => 'test@hyperf.io', 'name' => 'hyperf']);

        // Never here
        $this->assertFalse($result);
        $this->assertFalse($result2);
    }

    public function testAffectingStatementWithWrongSql()
    {
        $connection = ApplicationContext::getContainer()->get(ConnectionResolverInterface::class)->connection();

        $this->expectException(QueryException::class);

        $connection->affectingStatement('UPDATE xx SET x = 1 WHERE id = 1');
    }

    public function testCreateConnectionTimedOut()
    {
        $factory = new ConnectionFactory(ApplicationContext::getContainer());

        $connection = $factory->make([
            'driver' => 'pgsql-swoole',
            'host' => 'non-existent-host.internal',
            'port' => 5432,
            'database' => 'postgres',
            'username' => 'postgres',
            'password' => 'postgres',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Create connection failed, Please check the database configuration.');

        $connection->affectingStatement('UPDATE xx SET x = 1 WHERE id = 1');
    }

    public function testCreateTableForMigration()
    {
        $queryCommentSQL = "select a.attname,
    d.description
from pg_class c,
     pg_attribute a,
     pg_type t,
     pg_description d
where c.relname = 'password_resets_for_pgsql'
  and a.attnum > 0
  and a.attrelid = c.oid
  and a.atttypid = t.oid
  and d.objoid = a.attrelid
  and d.objsubid = a.attnum";

        $schema = new Schema();

        $this->migrator->run([__DIR__ . '/../migrations/one']);
        $this->assertTrue($schema->hasTable('password_resets_for_pgsql'));
        $this->assertSame('', $schema->connection()->selectOne($queryCommentSQL)['description'] ?? '');

        $this->migrator->run([__DIR__ . '/../migrations/two']);
        $this->assertSame('邮箱', $schema->connection()->selectOne($queryCommentSQL)['description'] ?? '');
    }
}
