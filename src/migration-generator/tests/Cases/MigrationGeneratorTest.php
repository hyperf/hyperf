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
namespace HyperfTest\MigrationGenerator\Cases;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\MigrationGenerator\MigrationGenerator;
use HyperfTest\MigrationGenerator\ContainerStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MigrationGeneratorTest extends TestCase
{
    public function testGenerateDefault()
    {
        $generator = $this->getGenerator();

        $generator->generate('default', __DIR__, 'book');

        $code = array_shift(ContainerStub::$codes);

        $this->assertNotEmpty($code);
    }

    public function testGenerateIndexes()
    {
        $generator = $this->getGenerator();

        $generator->generate('default', __DIR__, 'user_role');

        $code = array_shift(ContainerStub::$codes);

        $this->assertNotEmpty($code);
        $this->assertStringNotContainsString('primary', $code);
        $this->assertStringContainsString("\$table->index(['user_id'], 'INDEX_USER_ID');", $code);
        $this->assertStringContainsString("\$table->unique(['role_id', 'user_id'], 'INDEX_ROLE_ID');", $code);
    }

    public function testGenerateComment()
    {
        $generator = $this->getGenerator();

        $generator->generate('default', __DIR__, 'user');

        $code = array_shift(ContainerStub::$codes);

        $this->assertNotEmpty($code);
        $this->assertStringContainsString("\$table->comment('用户表');", $code);
    }

    protected function getGenerator(): MigrationGenerator
    {
        $container = ContainerStub::getContainer();

        return new MigrationGenerator(
            $container->get(ConnectionResolverInterface::class),
            $container->get(ConfigInterface::class)
        );
    }
}
