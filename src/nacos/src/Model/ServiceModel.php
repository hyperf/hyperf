<?php

namespace Hyperf\Nacos\Model;

class ServiceModel extends AbstractModel
{
    public $serviceName;

    public $groupName;

    public $namespaceId;

    public $protectThreshold = 0;

    public $metadata;

    public $selector;

    public $required_field = ['serviceName'];
}
