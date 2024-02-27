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
namespace HyperfTest\ConfigApollo;

use Hyperf\ConfigApollo\Option;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyperf\ConfigApollo\Option::class)]
class OptionTest extends TestCase
{
    public function testBuildUrl()
    {
        $option = new Option();
        $option->setServer('http://127.0.0.1:8080')->setAppid('test')->setCluster('default')->setClientIp('127.0.0.1');
        $baseUrl = 'http://127.0.0.1:8080/configs/test/default/';
        $this->assertSame($baseUrl, $option->buildBaseUrl());
        // Server without 'http://'
        $option->setServer('127.0.0.1:8080');
        $this->assertSame($baseUrl, $option->buildBaseUrl());
    }
}
