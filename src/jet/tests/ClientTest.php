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
namespace HyperfTest\Jet;

use Hyperf\Jet\Exception\ServerException;
use Hyperf\Jet\Packer\JsonLengthPacker;
use Hyperf\Rpc\Contract\TransporterInterface;
use HyperfTest\Jet\Stub\IdGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testSendAndRecv()
    {
        $packer = new JsonLengthPacker();
        $id = uniqid();
        $transporter = \Mockery::mock(TransporterInterface::class);
        $transporter->shouldReceive('send')->withAnyArgs()->andReturnUsing(function ($string) use ($packer, $id) {
            $data = $packer->unpack($string);
            $this->assertSame([$id], $data['params']);
            $this->assertSame('/id_generate/id', $data['method']);
        });
        $transporter->shouldReceive('recv')->andReturnUsing(function () use ($packer) {
            return $packer->pack([
                'jsonrpc' => '2.0',
                'id' => uniqid(),
                'result' => 'Hello Hyperf.',
                'context' => [],
            ]);
        });
        $client = new IdGenerator('IdGenerateService', $transporter, $packer);
        $ret = $client->id($id);
        $this->assertSame('Hello Hyperf.', $ret);
    }

    public function testException()
    {
        $packer = new JsonLengthPacker();
        $transporter = \Mockery::mock(TransporterInterface::class);
        $transporter->shouldReceive('send')->withAnyArgs()->andReturnUsing(function ($string) use ($packer) {
            $data = $packer->unpack($string);
            $this->assertSame('/id_generate/exception', $data['method']);
        });
        $transporter->shouldReceive('recv')->andReturnUsing(function () use ($packer) {
            return $packer->pack([
                'jsonrpc' => '2.0',
                'id' => uniqid(),
                'error' => [
                    'code' => 500,
                    'message' => 'Internal Server Error',
                    'data' => [],
                ],
                'context' => [],
            ]);
        });
        $client = new IdGenerator('IdGenerateService', $transporter, $packer);

        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Internal Server Error');
        $client->exception();
    }
}
