<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Nacos\Lib;

use Hyperf\Utils\Codec\Json;

class NacosOperator extends AbstractNacos
{
    public function getSwitches(): array
    {
        $response = $this->request('GET', '/nacos/v1/ns/operator/switches');

        return Json::decode($response->getBody()->getContents());
    }

    public function updateSwitches($entry, $value, $debug = false): array
    {
        $_debug = $debug ? 'true' : 'false';

        $response = $this->request('PUT', "/nacos/v1/ns/operator/switches?entry={$entry}&value={$value}&debug={$_debug}");

        return Json::decode($response->getBody()->getContents());
    }

    public function getMetrics(): array
    {
        $response = $this->request('GET', '/nacos/v1/ns/operator/metrics');

        return Json::decode($response->getBody()->getContents());
    }

    public function getServers($healthy = true): array
    {
        $_healthy = $healthy ? 'true' : 'false';

        $response = $this->request('GET', "/nacos/v1/ns/operator/servers?healthy={$_healthy}");

        return Json::decode($response->getBody()->getContents());
    }

    public function getLeader(): array
    {
        $response = $this->request('GET', '/nacos/v1/ns/raft/leader');

        return Json::decode($response->getBody()->getContents());
    }
}
