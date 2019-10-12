<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Utils;

use Hyperf\Utils\Json;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonTest extends TestCase
{
    public function testEncode()
    {
        $data = [
            'name' => 'Hyperf',
        ];
        $json = '{"name":"Hyperf"}';

        $this->assertSame($json, Json::encode($data));
    }

    public function testDecode()
    {
        $data = [
            'name' => 'Hyperf',
        ];
        $json = '{"name":"Hyperf"}';

        $this->assertSame($data, Json::decode($json));
    }

    /**
     * @expectedException \Hyperf\Utils\Exception\InvalidArgumentException
     */
    public function testDecodeException()
    {
        $data = [
            'name' => 'Hyperf',
        ];
        $json = '{"name":"Hyperf}';
        $this->assertSame($data, Json::decode($json));
    }
}
