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
namespace HyperfTest\SocketIOServer\Cases;

use Hyperf\SocketIOServer\Parser\Decoder;

/**
 * @internal
 * @coversNothing
 */
class DecoderTest extends AbstractTestCase
{
    public function testDecode()
    {
        $decoder = new Decoder();
        $packet = $decoder->decode('2["foo","bar"]');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/', $packet['nsp']);
        $this->assertEquals(['foo', 'bar'], $packet['data']);
        $packet = $decoder->decode('2/ws,["foo","bar"]');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/ws', $packet['nsp']);
        $this->assertEquals(['foo', 'bar'], $packet['data']);
        $packet = $decoder->decode('2/ws,15["foo","bar"]');
        $this->assertEquals('15', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/ws', $packet['nsp']);
        $this->assertEquals(['foo', 'bar'], $packet['data']);
        $packet = $decoder->decode('215["foo","bar"]');
        $this->assertEquals('15', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/', $packet['nsp']);
        $this->assertEquals(['foo', 'bar'], $packet['data']);
        $packet = $decoder->decode('215');
        $this->assertEquals('15', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/', $packet['nsp']);
        $this->assertEquals([], $packet['data']);
        $packet = $decoder->decode('1');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('1', $packet['type']);
        $this->assertEquals('/', $packet['nsp']);
        $this->assertEquals([], $packet['data']);
        $packet = $decoder->decode('1/ws?foo=bar&baz=1,');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('1', $packet['type']);
        $this->assertEquals('/ws', $packet['nsp']);
        $this->assertEquals(['foo' => 'bar', 'baz' => '1'], $packet['query']);
    }
}
