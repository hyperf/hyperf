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
class NacosInstanceTest extends AbstractTestCase
{
    public function testGetInstanceList()
    {
        $application = new Application(new Config([
            'guzzle_config' => [
                'handler' => new HandlerMockery(),
                'headers' => [
                    'charset' => 'UTF-8',
                ],
            ],
        ]));
        $result = $application->instance->list('nacos.test.1', []);
        $result = Json::decode((string) $result->getBody());
        $this->assertSame('nacos.test.1', $result['dom']);
    }

    public function testGetInstanceDetail()
    {
        $application = new Application(new Config([
            'guzzle_config' => [
                'handler' => new HandlerMockery(),
                'headers' => [
                    'charset' => 'UTF-8',
                ],
            ],
        ]));
        $result = $application->instance->detail('0.10.10.10', 8888, 'nacos.test.2');
        $result = Json::decode((string) $result->getBody());
        $this->assertSame('nacos.test.2', $result['service']);
    }
}
