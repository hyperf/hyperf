<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Cases;

use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\Parser\Packet;

/**
 * @internal
 * @coversNothing
 */
class EncoderTest extends AbstractTestCase
{
    public function testEncode()
    {
        $encoder = new Encoder();
        $packet = Packet::create([
            'id' => '',
            'nsp' => '/',
            'type' => Packet::OPEN,
            'data' => '',
        ]);
        $this->assertEquals('0', $encoder->encode($packet));
        $packet = Packet::create([
            'id' => '',
            'nsp' => '',
            'type' => Packet::OPEN,
            'data' => '',
        ]);
        $this->assertEquals('0', $encoder->encode($packet));
        $packet = Packet::create([
            'id' => '12',
            'nsp' => '/ws',
            'type' => Packet::EVENT,
            'data' => ['fake', 'data'],
        ]);
        $this->assertEquals('2/ws,12["fake","data"]', $encoder->encode($packet));
        $packet = Packet::create([
            'id' => '12',
            'nsp' => '/ws',
            'type' => Packet::ACK,
            'data' => ['fake', 'data'],
        ]);
        $this->assertEquals('3/ws,12["fake","data"]', $encoder->encode($packet));
        $packet = Packet::create([
            'id' => '12',
            'nsp' => '/ws',
            'type' => Packet::EVENT,
            'data' => false,
        ]);
        $this->assertEquals('2/ws,12', $encoder->encode($packet));
        $packet = Packet::create([
            'id' => '12',
            'nsp' => '/-!@#$%^&*()',
            'type' => Packet::EVENT,
            'data' => false,
        ]);
        $this->assertEquals('2/-!@#$%^&*(),12', $encoder->encode($packet));
        $packet = Packet::create([
            'type' => Packet::CLOSE,
        ]);
        $this->assertEquals('1', $encoder->encode($packet));
    }
}
