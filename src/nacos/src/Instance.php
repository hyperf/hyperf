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
namespace Hyperf\Nacos;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Exception\InvalidArgumentException;
use Hyperf\Nacos\Model\InstanceModel;
use Hyperf\Utils\Str;

class Instance extends InstanceModel
{
    public function __construct(ConfigInterface $config)
    {
        $client = $config->get('nacos.client', []);
        if (! isset($client['service_name'])) {
            throw new InvalidArgumentException('nacos.client.service_name is required');
        }

        foreach ($client as $key => $val) {
            $key = Str::camel($key);
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }

        $this->ip = current(swoole_get_local_ip());
        $this->port = $config->get('server.servers.0.port');
    }
}
