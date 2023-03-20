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
        return [
            'charset' => 'utf-8',
            'exConfigInfo' => 'true',
            'Client-RequestToken' => '1882376663d9d3236d8b71654a037af7',
            'Client-RequestTS' => '1679204269662',
            'Timestamp' => '1679204269662',
            'Spas-Signature' => 'PZqVeU8aUslLyV6tkuAG6qgjLKI=',
            'Client-AppName' => '',
        ];
    }
}
