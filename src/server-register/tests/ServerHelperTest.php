<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\ServerRegister;

use Hyperf\Config\Config;
use Hyperf\ServerRegister\ServerHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ServerHelperTest extends TestCase
{
    public function testGetInternalIp()
    {
        $config = new Config([
            'server_register' => [
                'host' => $host = '127.0.0.1',
            ],
        ]);

        $helper = new ServerHelper($config);
        $this->assertSame($host, $helper->getInternalIp());

        $config = new Config([
            'server_register' => [
                'host' => $host = function () {
                    return '127.0.0.2';
                },
            ],
        ]);

        $helper = new ServerHelper($config);
        $this->assertSame($host(), $helper->getInternalIp());

        $config = new Config([
            'server_register' => [],
        ]);

        $helper = new ServerHelper($config);
        $this->assertSame(current(swoole_get_local_ip()), $helper->getInternalIp());
    }
}
