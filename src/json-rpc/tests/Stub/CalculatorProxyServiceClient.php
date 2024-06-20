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

namespace HyperfTest\JsonRpc\Stub;

use Hyperf\RpcClient\Proxy\AbstractProxyService;

class CalculatorProxyServiceClient extends AbstractProxyService implements CalculatorServiceInterface
{
    public function add(int $a, int $b)
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }

    public function sum(IntegerValue $a, IntegerValue $b): IntegerValue
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }

    public function divide($value, $divider)
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }

    public function array(int $a, int $b): array
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }

    public function error()
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }

    public function getString(): ?string
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }

    public function callable(callable $a, ?callable $b): array
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }

    public function null()
    {
        return $this->client->__call(__FUNCTION__, func_get_args());
    }
}
