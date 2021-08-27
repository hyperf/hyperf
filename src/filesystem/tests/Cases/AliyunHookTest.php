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
        });
    }
}
