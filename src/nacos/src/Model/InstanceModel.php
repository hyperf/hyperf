<?php

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
