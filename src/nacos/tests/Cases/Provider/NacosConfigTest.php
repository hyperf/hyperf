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

/**
 * @internal
 * @coversNothing
 */
class NacosConfigTest extends AbstractTestCase
{
    public function testGet()
    {
        $application = new Application(new Config([
            'guzzle_config' => [
                'handler' => new HandlerMockery(),
                'headers' => [
                    'charset' => 'UTF-8',
                ],
            ],
        ]));
        $result = $application->config->get('hyperf-service-config', 'DEFAULT_GROUP');
        $this->assertSame(['A' => 'A'], Json::decode((string) $result->getBody()));
    }
}
