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

use Hyperf\Rpc\Context;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\RpcClient\Exception\RequestException;
use Hyperf\RpcMultiplex\Contract\DataFetcherInterface;
use Hyperf\Utils\Codec\Json;

class DataFormatter implements DataFormatterInterface, DataFetcherInterface
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function formatRequest($data)
    {
        [$path, $params, $id] = $data;
        return [
            Constant::ID => $id,
            Constant::PATH => $path,
            Constant::DATA => $params,
            Constant::CONTEXT => $this->context->getData(),
        ];
    }

    public function formatResponse($data)
    {
        [$id, $result] = $data;
        return [
            Constant::ID => $id,
            Constant::RESULT => $result,
            Constant::CONTEXT => $this->context->getData(),
        ];
    }

    public function formatErrorResponse($data)
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
            Constant::ID => $id ?? null,
            Constant::ERROR => [
                Constant::CODE => $code,
                Constant::MESSAGE => $message,
                Constant::DATA => $data,
            ],
            Constant::CONTEXT => $this->context->getData(),
        ];
    }

    public function fetch(array $data)
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
