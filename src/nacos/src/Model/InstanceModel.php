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

    /**
     * @var string
     */
    public $serviceName;

    /**
     * @var string
     */
    public $groupName;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var int
     */
    public $port;

    /**
     * @var string
     */
    public $clusterName;

    /**
     * @var string
     */
    public $namespaceId;

    /**
     * @var double|float|int
     */
    public $weight;

    /**
     * @var string
     */
    public $metadata;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var bool
     */
    public $ephemeral;

    /**
     * @var bool
     */
    public $healthy;

    /**
     * @var string[]
     */
    public $requiredFields = [
        'ip',
        'port',
        'serviceName',
    ];
}
