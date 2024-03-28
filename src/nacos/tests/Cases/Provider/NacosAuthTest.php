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

namespace HyperfTest\Nacos\Cases\Provider;

use Hyperf\Codec\Json;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use HyperfTest\Nacos\AbstractTestCase;
use HyperfTest\Nacos\HandlerMockery;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class NacosAuthTest extends AbstractTestCase
{
    public function testLogin()
    {
        $application = new Application(new Config([
            'username' => 'nacos',
            'password' => 'nacos',
            'guzzle_config' => [
                'handler' => new HandlerMockery(),
                'headers' => [
                    'charset' => 'UTF-8',
                ],
            ],
        ]));
        $result = $application->auth->login('nacos', 'nacos');
        $result = Json::decode((string) $result->getBody());
        $this->assertSame($result['accessToken'], $application->auth->getAccessToken());
        $this->assertSame($result['tokenTtl'], 18000);
        $this->assertSame($result['globalAdmin'], true);
    }
}
