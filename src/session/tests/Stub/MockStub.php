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

namespace HyperfTest\Session\Stub;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;

class MockStub
{
    public static function makeConfig(): ConfigInterface
    {
        return new Config([
            'session' => [
                'handler' => Handler\FileHandler::class,
                'options' => [
                    'connection' => 'default',
                    'path' => BASE_PATH . '/runtime/session',
                    'gc_maxlifetime' => 1200,
                    'session_name' => 'HYPERF_SESSION_ID',
                ],
            ],
        ]);
    }
}
