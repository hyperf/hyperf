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
namespace HyperfTest\Filesystem\Cases;

use Hyperf\Utils\ResourceGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AliyunHookTest extends TestCase
{
    /**
     * @group NonCoroutine
     */
    public function testIsResource()
    {
        run(function () {
            $rs = ResourceGenerator::from('foo');
            $this->assertTrue(\Oss\OssClient\is_resource($rs));

            $rs = curl_init();
            if (version_compare(SWOOLE_VERSION, '4.6.0', '<')) {
                $this->assertInstanceOf(\Swoole\Curl\Handler::class, $rs);
                $this->assertTrue(\Oss\OssClient\is_resource($rs));
            } elseif (PHP_VERSION_ID > 80000) {
                if (SWOOLE_VERSION_ID >= 40802) {
                    $this->assertInstanceOf(\CurlHandle::class, $rs);
                } else {
                    $this->assertInstanceOf(\Swoole\Coroutine\Curl\Handle::class, $rs);
                    $this->assertTrue(\Oss\OssClient\is_resource($rs));
                }
            }
        });

        run(function () {
            $rs = ResourceGenerator::from('foo');
            $this->assertTrue(\Oss\OssClient\is_resource($rs));
        }, 0);
    }
}
