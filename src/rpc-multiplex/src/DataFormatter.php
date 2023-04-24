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
namespace Hyperf\RpcMultiplex;

use Hyperf\Codec\Json;
use Hyperf\Rpc\Context;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\ErrorResponse;
use Hyperf\Rpc\Request;
use Hyperf\Rpc\Response;
use Hyperf\RpcClient\Exception\RequestException;
use Hyperf\RpcMultiplex\Contract\DataFetcherInterface;
use Throwable;

class DataFormatter implements DataFormatterInterface, DataFetcherInterface
{
    protected Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function formatRequest(Request $request): array
    {
        return [
            Constant::ID => $request->getId(),
            Constant::PATH => $request->getPath(),
            Constant::DATA => $request->getParams(),
            Constant::CONTEXT => $this->context->getData(),
        ];
    }

    public function formatResponse(Response $response): array
    {
        return [
            Constant::ID => $response->getId(),
            Constant::RESULT => $response->getResult(),
            Constant::CONTEXT => $this->context->getData(),
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
            Constant::ID => $response->getId(),
            Constant::ERROR => [
                Constant::CODE => $response->getCode(),
                Constant::MESSAGE => $response->getMessage(),
                Constant::DATA => $exception,
            ],
            Constant::CONTEXT => $this->context->getData(),
        ];
    }

    public function fetch(array $data): mixed
    {
        if (array_key_exists(Constant::DATA, $data)) {
            $this->context->setData($data[Constant::CONTEXT] ?? []);

            return $data[Constant::DATA];
        }

        if (array_key_exists(Constant::ERROR, $data)) {
            throw new RequestException(
                $data[Constant::ERROR][Constant::MESSAGE] ?? 'Invalid error message',
                $data[Constant::ERROR][Constant::CODE] ?? 0,
                $data[Constant::ERROR][Constant::DATA],
            );
        }

        throw new RequestException('Unknown data ' . Json::encode($data), 0);
    }
}
