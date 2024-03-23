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

namespace HyperfTest\Server;

use Hyperf\Server\Port;
use Hyperf\Server\Server;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PortTest extends TestCase
{
    public function testSetting()
    {
        $port = Port::build([
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
        ]);

        $this->assertSame([], $port->getSettings());

        $port = Port::build([
            'name' => 'tcp',
            'type' => Server::SERVER_BASE,
        ]);

        $this->assertSame([
            'open_http2_protocol' => false,
            'open_http_protocol' => false,
        ], $port->getSettings());

        $port = Port::build([
            'name' => 'tcp',
            'type' => Server::SERVER_BASE,
            'settings' => [
                'open_http2_protocol' => true,
            ],
        ]);

        $this->assertSame([
            'open_http2_protocol' => true,
            'open_http_protocol' => false,
        ], $port->getSettings());
    }
}
