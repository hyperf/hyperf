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

namespace Hyperf\Nacos\Provider;

use GuzzleHttp\RequestOptions;
use Hyperf\Nacos\AbstractProvider;
use Psr\Http\Message\ResponseInterface;

class OperatorProvider extends AbstractProvider
{
    public function getSwitches(): ResponseInterface
    {
        return $this->request('GET', 'nacos/v1/ns/operator/switches');
    }

    public function updateSwitches(string $entry, string $value, ?bool $debug = null): ResponseInterface
    {
        return $this->request('PUT', 'nacos/v1/ns/operator/switches', [
            RequestOptions::QUERY => $this->filter([
                'entry' => $entry,
                'value' => $value,
                'debug' => $debug,
            ]),
        ]);
    }

    public function getMetrics(): ResponseInterface
    {
        return $this->request('GET', 'nacos/v1/ns/operator/metrics');
    }

    public function getServers(?bool $healthy = null): ResponseInterface
    {
        return $this->request('GET', 'nacos/v1/ns/operator/servers', [
            RequestOptions::QUERY => $this->filter([
                'healthy' => $healthy,
            ]),
        ]);
    }

    public function getLeader(): ResponseInterface
    {
        return $this->request('GET', 'nacos/v1/ns/raft/leader');
    }
}
