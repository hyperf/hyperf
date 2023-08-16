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
namespace Hyperf\Nacos\Protobuf\Request;

abstract class Request implements RequestInterface
{
    protected function defaultHeaders(): array
    {
        $time = (string) (time() * 1000);
        return [
            'charset' => 'utf-8',
            'exConfigInfo' => 'true',
            'Client-RequestToken' => md5($time),
            'Client-RequestTS' => $time,
            'Timestamp' => $time,
            // 'Spas-Signature' => 'PZqVeU8aUslLyV6tkuAG6qgjLKI=',
            'Client-AppName' => '',
        ];
    }
}
