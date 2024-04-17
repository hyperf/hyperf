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
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DecoderTest extends AbstractTestCase
{
    public function testDecode()
    {
        $decoder = new Decoder();

        $packet = $decoder->decode('42["foo","bar"]');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/', $packet['nsp']);
        $this->assertEquals(['foo', 'bar'], $packet['data']);
        $packet = $decoder->decode('42/ws,["foo","bar"]');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/ws', $packet['nsp']);
        $this->assertEquals(['foo', 'bar'], $packet['data']);
        $packet = $decoder->decode('42/ws,15["foo","bar"]');
        $this->assertEquals('15', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/ws', $packet['nsp']);
        $this->assertEquals(['foo', 'bar'], $packet['data']);
        $packet = $decoder->decode('4215["foo","bar"]');
        $this->assertEquals('15', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/', $packet['nsp']);
        $this->assertEquals(['foo', 'bar'], $packet['data']);
        $packet = $decoder->decode('4215');
        $this->assertEquals('15', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/', $packet['nsp']);
        $this->assertEquals([], $packet['data']);
        $packet = $decoder->decode('41');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('1', $packet['type']);
        $this->assertEquals('/', $packet['nsp']);
        $this->assertEquals([], $packet['data']);
        $packet = $decoder->decode('41/ws?foo=bar&baz=1,');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('1', $packet['type']);
        $this->assertEquals('/ws', $packet['nsp']);
        $this->assertEquals(['foo' => 'bar', 'baz' => '1'], $packet['query']);
        $result = $decoder->decode('42/1');
        $this->assertEquals(2, $result->type);
        $this->assertEquals('/1', $result->nsp);
        $result = $decoder->decode('42/1,2["event","hellohyperf"]');
        $this->assertEquals(2, $result->type);
        $this->assertEquals('/1', $result->nsp);
        $this->assertEquals('2', $result->id);
        $this->assertEquals([
        ], $result->query);
        $result = $decoder->decode('42/1?foo=xxx,2["event","hellohyperf"]');
        $this->assertEquals(2, $result->type);
        $this->assertEquals('/1', $result->nsp);
        $this->assertEquals('2', $result->id);
        $this->assertEquals([
            'foo' => 'xxx',
        ], $result->query);
        $this->assertEquals(json_decode('["event","hellohyperf"]', true), $result->data);

        $result = $decoder->decode('42/1?foo=xxx,2{"event": "JOIN"}');
        $this->assertEquals(2, $result->type);
        $this->assertEquals('/1', $result->nsp);
        $this->assertEquals('2', $result->id);
        $this->assertEquals([
            'foo' => 'xxx',
        ], $result->query);
        $this->assertEquals(['event' => 'JOIN'], $result->data);

        $result = $decoder->decode('42/1?foo=xxx&bar=yyy');
        $this->assertEquals(2, $result->type);
        $this->assertEquals('/1', $result->nsp);
        $this->assertEquals('', $result->id);
        $this->assertEquals([
            'foo' => 'xxx',
            'bar' => 'yyy',
        ], $result->query);

        try {
            $decoder->decode('42/1?2["event","hellohyperf"]');
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Invalid data', $e->getMessage());
        }

        $packet = $decoder->decode('42/x');
        $this->assertEquals('', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/x', $packet['nsp']);
        $this->assertEquals([], $packet['data']);
        $this->assertEquals([], $packet['query']);

        try {
            $decoder->decode('2/1?2["event","hellohyperf"]');
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Invalid engine 2', $e->getMessage());
        }

        try {
            $decoder->decode('4');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Empty packet', $e->getMessage());
        }

        $packet = $decoder->decode('42/test,3["TEST_EVENT",{"url": "https://hyperf.wiki/3.0/#/zh-cn/quick-start/install?foo=bar&baz=1"}]');
        $this->assertEquals('3', $packet['id']);
        $this->assertEquals('2', $packet['type']);
        $this->assertEquals('/test', $packet['nsp']);
        $this->assertEquals(['TEST_EVENT', ['url' => 'https://hyperf.wiki/3.0/#/zh-cn/quick-start/install?foo=bar&baz=1']], $packet['data']);
        $this->assertEquals([], $packet['query']);
    }
}
