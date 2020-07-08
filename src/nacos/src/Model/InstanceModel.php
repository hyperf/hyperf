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
namespace Hyperf\Nacos\Model;

class InstanceModel extends AbstractModel
{
    public $serviceName;

    public $groupName;

    public $ip;

    public $port;

    public $clusterName;

    public $namespaceId;

    public $weight;

    public $metadata;

    public $enabled;

    public $ephemeral;

    public $healthy;

    public $required_field = [
        'ip',
        'port',
        'serviceName',
    ];
}
