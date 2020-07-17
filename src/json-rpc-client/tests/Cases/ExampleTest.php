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
namespace HyperfTest\JsonRpcClient\Cases;

use Hyperf\JsonRpcClient\Exception\ServerException;
use Hyperf\JsonRpcClient\Packer\JsonLengthPacker;
use Hyperf\JsonRpcClient\Transporter\TcpTransporter;
use HyperfTest\Stub\IdGenerator;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends AbstractTestCase
{
    public function testExample()
    {
        $this->assertTrue(true);

        // $client = new IdGenerator('IdGenerateService', new TcpTransporter('127.0.0.1', 9502), new JsonLengthPacker());
        //
        // $ret = $client->id($id = uniqid());
        // $this->assertStringContainsString($id, $ret);
        //
        // $this->expectException(ServerException::class);
        // $this->expectExceptionCode(500);
        // $this->expectExceptionMessage('Inner Server Error');
        // $client->exception();
    }
}
