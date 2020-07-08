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
namespace Hyperf\Nacos;

use Hyperf\Nacos\Model\InstanceModel;

class ThisInstance extends InstanceModel
{
    public function __construct()
    {
        $this->ip = current(swoole_get_local_ip());
        $this->port = config('server.servers.0.port');
        $client = config('nacos.client', []);
        if (! isset($client['serviceName'])) {
            throw new \Exception('nacos.client.serviceName is required');
        }
        unset($client['ip'], $client['port']);
        foreach ($client as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }
}
