<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Provider;

use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class ServiceProvider extends AbstractProvider
{
    public function registry(string $registryKey, string $registryValue): ResponseInterface
    {
        $body = [
            'registryGroup' => 'EXECUTOR',
            'registryKey' => $registryKey,
            'registryValue' => $registryValue,
        ];
        return $this->request('POST', '/api/registry', [
            RequestOptions::JSON => $body,
        ]);
    }

    public function registryRemove(string $registryKey, string $registryValue): ResponseInterface
    {
        $body = [
            'registryGroup' => 'EXECUTOR',
            'registryKey' => $registryKey,
            'registryValue' => $registryValue,
        ];
        return $this->request('POST', '/api/registryRemove', [
            RequestOptions::JSON => $body,
        ]);
    }

    public function callback(int $logId, int $logDateTim, int $handleCode = 200, $handleMsg = null): ResponseInterface
    {
        $body = [[
            'logId' => $logId,
            'logDateTim' => $logDateTim,
            'handleCode' => $handleCode,
            'handleMsg' => $handleMsg,
        ]];
        return $this->request('POST', '/api/callback', [
            RequestOptions::JSON => $body,
        ]);
    }
}
