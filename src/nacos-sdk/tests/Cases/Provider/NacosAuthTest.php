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
namespace HyperfTest\NacosSdk\Cases\Provider;

use Hyperf\NacosSdk\Application;
use Hyperf\NacosSdk\Config;
use Hyperf\Utils\Codec\Json;
use HyperfTest\NacosSdk\AbstractTestCase;
use HyperfTest\NacosSdk\HandlerMockery;

/**
 * @internal
 * @coversNothing
 */
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
