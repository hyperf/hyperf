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
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Migrations\DatabaseMigrationRepository;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Grammars\MySqlGrammar;
use Hyperf\Database\Schema\Schema;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DatabaseMigratorIntegrationTest extends TestCase
{
    protected $migrator;

    protected function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with('db.connector.mysql')->andReturn(new MySqlConnector());
        $connector = new ConnectionFactory($container);

        $dbConfig = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'hyperf',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ];

        $connection = $connector->make($dbConfig);
        $connection2 = $connector->make(array_merge($dbConfig, ['database' => 'hyperf2']));
        $connection3 = $connector->make(array_merge($dbConfig, ['database' => 'hyperf3']));

        $resolver = new ConnectionResolver(['default' => $connection, 'mysql2' => $connection2, 'mysql3' => $connection3]);

        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver);

        ApplicationContext::setContainer($container);

        $this->migrator = new Migrator(
            $repository = new DatabaseMigrationRepository($resolver, 'migrations'),
            $resolver,
            new Filesystem()
        );

        $output = m::mock(OutputStyle::class);
        $output->shouldReceive('writeln');

        $this->migrator->setOutput($output);

        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }
    }

    public function testCreateTableforMigration()
    {
        $schema = new Schema();

        $this->migrator->rollback([__DIR__ . '/migrations/one']);
        $this->migrator->run([__DIR__ . '/migrations/one']);

        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));

        $res = (array) $schema->connection()->selectOne('SHOW CREATE TABLE users;');
        $sql = $res['Create Table'];
        $asserts = [
            "CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sex` enum('0','1','2') COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Users Table'",
            "CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `sex` enum('0','1','2') COLLATE utf8mb3_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='Users Table'",
        ];

        $this->assertTrue(in_array($sql, $asserts, true));

        $res = (array) $schema->connection()->selectOne('SHOW CREATE TABLE password_resets;');
        $sql = $res['Create Table'];
        $asserts = [
            'CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci',
            'CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci',
        ];
        $this->assertTrue(in_array($sql, $asserts, true));
    }

    public function testBasicMigrationOfSingleFolder()
    {
        $schema = new Schema();

        $this->migrator->rollback([__DIR__ . '/migrations/one']);

        $ran = $this->migrator->run([__DIR__ . '/migrations/one']);

        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));

        $this->assertTrue(Str::contains($ran[0], 'users'));
        $this->assertTrue(Str::contains($ran[1], 'password_resets'));
    }

    public function testMigrationsCanBeRolledBack()
    {
        $schema = new Schema();
        $this->migrator->run([__DIR__ . '/migrations/one']);
        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));
        $rolledBack = $this->migrator->rollback([__DIR__ . '/migrations/one']);
        $this->assertFalse($schema->hasTable('users'));
        $this->assertFalse($schema->hasTable('password_resets'));

        $this->assertTrue(Str::contains($rolledBack[0], 'password_resets'));
        $this->assertTrue(Str::contains($rolledBack[1], 'users'));
    }

    public function testMigrationsCanBeReset()
    {
        $schema = new Schema();
        $this->migrator->run([__DIR__ . '/migrations/one']);
        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));
        $rolledBack = $this->migrator->reset([__DIR__ . '/migrations/one']);
        $this->assertFalse($schema->hasTable('users'));
        $this->assertFalse($schema->hasTable('password_resets'));

        $this->assertTrue(Str::contains($rolledBack[0], 'password_resets'));
        $this->assertTrue(Str::contains($rolledBack[1], 'users'));
    }

    public function testNoErrorIsThrownWhenNoOutstandingMigrationsExist()
    {
        $schema = new Schema();
        $this->migrator->run([__DIR__ . '/migrations/one']);
        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));
        $this->migrator->run([__DIR__ . '/migrations/one']);
    }

    public function testNoErrorIsThrownWhenNothingToRollback()
    {
        $schema = new Schema();
        $this->migrator->run([__DIR__ . '/migrations/one']);
        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));
        $this->migrator->rollback([__DIR__ . '/migrations/one']);
        $this->assertFalse($schema->hasTable('users'));
        $this->assertFalse($schema->hasTable('password_resets'));
        $this->migrator->rollback([__DIR__ . '/migrations/one']);
    }

    public function testMigrationsCanRunAcrossMultiplePaths()
    {
        $schema = new Schema();
        $this->migrator->run([__DIR__ . '/migrations/one', __DIR__ . '/migrations/two']);
        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));
        $this->assertTrue($schema->hasTable('flights'));
    }

    public function testMigrationsCanBeRolledBackAcrossMultiplePaths()
    {
        $schema = new Schema();
        $this->migrator->run([__DIR__ . '/migrations/one', __DIR__ . '/migrations/two']);
        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));
        $this->assertTrue($schema->hasTable('flights'));
        $this->migrator->rollback([__DIR__ . '/migrations/one', __DIR__ . '/migrations/two']);
        $this->assertFalse($schema->hasTable('users'));
        $this->assertFalse($schema->hasTable('password_resets'));
        $this->assertFalse($schema->hasTable('flights'));
    }

    public function testMigrationsCanBeResetAcrossMultiplePaths()
    {
        $schema = new Schema();
        $this->migrator->run([__DIR__ . '/migrations/one', __DIR__ . '/migrations/two']);
        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('password_resets'));
        $this->assertTrue($schema->hasTable('flights'));
        $this->migrator->reset([__DIR__ . '/migrations/one', __DIR__ . '/migrations/two']);
        $this->assertFalse($schema->hasTable('users'));
        $this->assertFalse($schema->hasTable('password_resets'));
        $this->assertFalse($schema->hasTable('flights'));
    }

    public function testChangingColumnsDoesntNeedCharacterOptions()
    {
        $builder = $this->getConnection()->getSchemaBuilder();
        $builder->create('test_change_types', function (Blueprint $table) {
            $table->string('a');
            $table->string('b');
            $table->string('c');
            $table->string('d');
            $table->string('e');
            $table->string('f');
            $table->string('g');
            $table->string('h');
            $table->string('i');
            $table->string('j');
            $table->string('k');
            $table->string('l');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        $blueprint = new Blueprint('test_change_types', function (Blueprint $table) {
            $table->bigInteger('a')->change();
            $table->binary('b')->change();
            $table->boolean('c')->change();
            $table->date('d')->change();
            $table->dateTime('e')->change();
            $table->decimal('f')->change();
            $table->float('g')->change();
            $table->integer('h')->change();
            $table->json('i')->change();
            $table->smallInteger('j')->change();
            $table->time('k')->change();
            $table->text('l')->change();
        });

        $queries = $blueprint->toSql($this->getConnection(), new MySqlGrammar());

        $this->assertSame(
            'ALTER TABLE test_change_types ' .
            'CHANGE a a BIGINT NOT NULL, ' .
            'CHANGE b b TINYBLOB NOT NULL, ' .
            'CHANGE c c TINYINT(1) NOT NULL, ' .
            'CHANGE d d DATE NOT NULL, ' .
            'CHANGE e e DATETIME NOT NULL, ' .
            'CHANGE f f NUMERIC(8, 2) NOT NULL, ' .
            'CHANGE g g DOUBLE PRECISION NOT NULL, ' .
            'CHANGE h h INT NOT NULL, ' .
            'CHANGE i i JSON NOT NULL, ' .
            'CHANGE j j SMALLINT NOT NULL, ' .
            'CHANGE k k TIME NOT NULL, ' .
            'CHANGE l l TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`',
            $queries[0]
        );

        $builder->drop('test_change_types');
    }

    public function testMigrationsCanEachDefineConnection()
    {
        $schema = new Schema();

        $ran = $this->migrator->run([__DIR__ . '/migrations/connection_configured']);
        $this->assertFalse($schema->hasTable('failed_jobs'));
        $this->assertFalse($schema->hasTable('jobs'));
        $this->assertFalse($schema->connection('mysql2')->getSchemaBuilder()->hasTable('failed_jobs'));
        $this->assertFalse($schema->connection('mysql2')->getSchemaBuilder()->hasTable('jobs'));
        $this->assertTrue($schema->connection('mysql3')->getSchemaBuilder()->hasTable('failed_jobs'));
        $this->assertTrue($schema->connection('mysql3')->getSchemaBuilder()->hasTable('jobs'));
        $this->migrator->rollback([__DIR__ . '/migrations/connection_configured']);

        $this->assertTrue(Str::contains($ran[0], 'failed_jobs'));
        $this->assertTrue(Str::contains($ran[1], 'jobs'));
    }

    protected function getConnection(): Connection
    {
        return $this->migrator->resolveConnection('default');
    }
}
