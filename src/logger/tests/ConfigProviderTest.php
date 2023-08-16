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
namespace HyperfTest\Logger;

use Hyperf\Logger\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Hyperf\Logger\ConfigProvider
 */
class ConfigProviderTest extends TestCase
{
    public function testInvoke()
    {
        $dir = str_replace('/tests', '/src', __DIR__);

        $this->assertSame([
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for logger.',
                    'source' => $dir . '/../publish/logger.php',
                    'destination' => BASE_PATH . '/config/autoload/logger.php',
                ],
            ],
        ], (new ConfigProvider())());
    }
}
