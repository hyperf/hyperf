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

namespace Hyperf\JsonRpc;

use Hyperf\Rpc\Context;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\ErrorResponse;
use Hyperf\Rpc\Request;
use Hyperf\Rpc\Response;
use Throwable;

class DataFormatter implements DataFormatterInterface
{
    public function __construct(protected Context $context)
    {
    }

    public function formatRequest(Request $request): array
    {
        return [
            'jsonrpc' => '2.0',
            'method' => $request->getPath(),
            'params' => $request->getParams(),
            'id' => $request->getId(),
            'context' => $this->context->getData(),
        ];
    }

    public function formatResponse(Response $response): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $response->getId(),
            'result' => $response->getResult(),
            'context' => $this->context->getData(),
        ];
    }

    public function formatErrorResponse(ErrorResponse $response): array
    {
        $exception = $response->getException();
        if ($exception instanceof Throwable) {
            $exception = [
                'class' => get_class($exception),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ];
        }
        return [
            'jsonrpc' => '2.0',
            'id' => $response->getId(),
            'error' => [
                'code' => $response->getCode(),
                'message' => $response->getMessage(),
                'data' => $exception,
            ],
            'context' => $this->context->getData(),
        ];
    }
}
