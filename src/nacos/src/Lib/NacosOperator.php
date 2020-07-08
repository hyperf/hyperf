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

class NacosOperator extends AbstractNacos
{
    public function getSwitches()
    {
        return $this->request('GET', '/nacos/v1/ns/operator/switches');
    }

    public function updateSwitches($entry, $value, $debug = false)
    {
        $_debug = $debug ? 'true' : 'false';
        return $this->request('PUT', "/nacos/v1/ns/operator/switches?entry={$entry}&value={$value}&debug={$_debug}");
    }

    public function getMetrics()
    {
        return $this->request('GET', '/nacos/v1/ns/operator/metrics');
    }

    public function getServers($healthy = true)
    {
        $_healthy = $healthy ? 'true' : 'false';

        return $this->request('GET', "/nacos/v1/ns/operator/servers?healthy={$_healthy}");
    }

    public function getLeader()
    {
        return $this->request('GET', '/nacos/v1/ns/raft/leader');
    }
}
