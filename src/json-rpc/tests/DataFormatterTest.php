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

namespace HyperfTest\JsonRpc;

use Hyperf\JsonRpc\DataFormatter;
use Hyperf\JsonRpc\NormalizeDataFormatter;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\Rpc\ErrorResponse;
use Hyperf\RpcClient\Exception\RequestException;
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\SymfonyNormalizer;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DataFormatterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        (new RpcContext())->clear();
    }

    public function testFormatErrorResponse()
    {
        $formatter = new DataFormatter($context = new RpcContext());
        $context->set('id', $cid = uniqid());
        $data = $formatter->formatErrorResponse(
            new ErrorResponse($id = uniqid(), 500, 'Error', new RuntimeException('test case', 1000))
        );

        $this->assertEquals([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => 500,
                'message' => 'Error',
                'data' => [
                    'class' => 'RuntimeException',
                    'code' => 1000,
                    'message' => 'test case',
                ],
            ],
            'context' => [
                'id' => $cid,
            ],
        ], $data);

        $exception = new RequestException('', 0, $data['error']['data']);
        $this->assertSame(1000, $exception->getThrowableCode());
        $this->assertSame('test case', $exception->getThrowableMessage());
    }

    public function testNormalizeFormatErrorResponse()
    {
        $normalizer = new SymfonyNormalizer((new SerializerFactory())());

        $formatter = new NormalizeDataFormatter($normalizer, new RpcContext());
        $data = $formatter->formatErrorResponse(
            new ErrorResponse($id = uniqid(), 500, 'Error', new RuntimeException('test case', 1000))
        );

        $this->assertArrayHasKey('line', $data['error']['data']['attributes']);
        $this->assertArrayHasKey('file', $data['error']['data']['attributes']);

        $exception = new RequestException('', 0, $data['error']['data']);
        $this->assertSame(1000, $exception->getThrowableCode());
        $this->assertSame('test case', $exception->getThrowableMessage());

        unset($data['error']['data']['attributes']['line'], $data['error']['data']['attributes']['file']);

        $this->assertEquals([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => 500,
                'message' => 'Error',
                'data' => [
                    'class' => 'RuntimeException',
                    'attributes' => [
                        'code' => 1000,
                        'message' => 'test case',
                    ],
                ],
            ],
            'context' => [],
        ], $data);
    }
}
