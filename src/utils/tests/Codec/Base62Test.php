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
namespace HyperfTest\Utils\Codec;

use Hyperf\Utils\Codec\Base62;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class Base62Test extends TestCase
{
    public function testEncode()
    {
        $this->assertEquals('fMYsmVDc', Base62::encode(145_667_762_035_560));
        $this->assertEquals(145_667_762_035_560, Base62::decode('fMYsmVDc'));
    }
}
