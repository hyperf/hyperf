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

use Hyperf\Utils\Str;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StrTest extends TestCase
{
    public function testSlug()
    {
        $res = Str::slug('hyperf_', '_');

        $this->assertSame('hyperf', $res);

        $arr = [
            '0' => 0,
            '1' => 1,
            'a' => 'a',
        ];

        $this->assertSame([0, 1, 'a' => 'a'], $arr);
        foreach ($arr as $i => $v) {
            $this->assertIsInt($i);
            break;
        }
    }
}
