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

namespace Hyperf\Testing\Concerns;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Testing\Constraint\HasInDatabase;
use PHPUnit\Framework\Constraint\LogicalNot;

trait InteractsWithDatabase
{
    protected function assertDatabaseHas(string $table, array $data, ?string $connection = null): void
    {
        $this->assertThat($data, new HasInDatabase($this->getConnection($connection), $table));
    }

    protected function assertDatabaseMissing(string $table, array $data, ?string $connection = null): void
    {
        $this->assertThat($data, new LogicalNot(new HasInDatabase($this->getConnection($connection), $table)));
    }

    private function getConnection(?string $name): ConnectionInterface
    {
        return ApplicationContext::getContainer()->get(ConnectionResolverInterface::class)->connection($name);
    }
}
