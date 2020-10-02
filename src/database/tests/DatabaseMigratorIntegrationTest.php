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

use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Migrations\DatabaseMigrationRepository;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Database\Schema\Schema;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Str;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @internal
 * @coversNothing
 */
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
            'host' => 'localhost',
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

    public function testBasicMigrationOfSingleFolder()
    {
        $schema = new Schema();
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
}
