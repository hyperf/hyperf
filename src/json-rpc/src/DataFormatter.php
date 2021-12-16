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

class DataFormatter implements DataFormatterInterface
{
    public function __construct(protected Context $context)
    {
    }

    public function formatRequest(array $data): array
    {
        [$path, $params, $id] = $data;
        return [
            'jsonrpc' => '2.0',
            'method' => $path,
            'params' => $params,
            'id' => $id,
            'context' => $this->context->getData(),
        ];
    }

    public function formatResponse(array $data): array
    {
        [$id, $result] = $data;
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
            'context' => $this->context->getData(),
        ];
    }

    public function formatErrorResponse(array $data): array
    {
        [$id, $code, $message, $data] = $data;

        if (isset($data) && $data instanceof \Throwable) {
            $data = [
                'class' => get_class($data),
                'code' => $data->getCode(),
                'message' => $data->getMessage(),
            ];
        }
        return [
            'jsonrpc' => '2.0',
            'id' => $id ?? null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ],
            'context' => $this->context->getData(),
        ];
    }
}
