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
class NacosServiceTest extends AbstractTestCase
{
    public function testServiceDetail()
    {
        $application = new Application(new Config([
            'guzzle_config' => [
                'handler' => new HandlerMockery(),
                'headers' => [
                    'charset' => 'UTF-8',
                ],
            ],
        ]));
        $result = $application->service->detail('nacos.test.2');
        $result = Json::decode((string) $result->getBody());
        $this->assertSame('nacos.test.2', $result['name']);
    }

    public function testServiceList()
    {
        $application = new Application(new Config([
            'guzzle_config' => [
                'handler' => new HandlerMockery(),
                'headers' => [
                    'charset' => 'UTF-8',
                ],
            ],
        ]));
        $result = $application->service->list(0, 10);
        $result = Json::decode((string) $result->getBody());
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('doms', $result);
    }
}
