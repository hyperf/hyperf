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
namespace HyperfTest\Utils;

use Hyperf\Utils\Resource;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ResourceTest extends TestCase
{
    public function testResourceFor()
    {
        $data = '123123';
        $resource = Resource::resourceFor($data);
        $this->assertSame('1', fread($resource, 1));
        $this->assertSame('23', fread($resource, 2));
        $this->assertSame('123', fread($resource, 10));
    }
}
