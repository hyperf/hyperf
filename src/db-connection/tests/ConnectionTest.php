<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\DbConnection;

use Hyperf\DbConnection\Connection;
use Hyperf\DbConnection\ConnectionResolver;
use HyperfTest\DbConnection\Stubs\ContainerStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConnectionTest extends TestCase
{
    public function testResolveConnection()
    {
        $container = ContainerStub::mockContainer();

        $resolver = $container->get(ConnectionResolver::class);

        $connection = $resolver->connection();

        $this->assertInstanceOf(Connection::class, $connection);
    }
}
