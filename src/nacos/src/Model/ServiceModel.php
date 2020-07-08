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
