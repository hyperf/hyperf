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
namespace Hyperf\Nacos\Service;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Exception\InvalidArgumentException;
use Hyperf\Nacos\Model\InstanceModel;
use Hyperf\Utils\Str;

class Instance extends InstanceModel
{
    public function __construct(ConfigInterface $config)
    {
        $serviceConfig = $config->get('nacos.service', []);
        if (! isset($serviceConfig['service_name'])) {
            throw new InvalidArgumentException('nacos.service.service_name is required');
        }

        parent::__construct($serviceConfig);

        $this->ephemeral = $serviceConfig['ephemeral'] ? "true" : "false";
        $this->ip = current(swoole_get_local_ip());
        $this->port = $config->get('server.servers.0.port');
    }
}
