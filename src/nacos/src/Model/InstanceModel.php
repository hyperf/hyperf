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
namespace Hyperf\Nacos\Model;

class InstanceModel extends AbstractModel
{
    /**
     * @var null|string
     */
    public $serviceName;

    /**
     * @var null|string
     */
    public $groupName;

    /**
     * @var null|string
     */
    public $ip;

    /**
     * @var null|int
     */
    public $port;

    /**
     * @var null|string
     */
    public $clusterName;

    /**
     * @var null|string
     */
    public $namespaceId;

    /**
     * @var null|float|float|int
     */
    public $weight;

    /**
     * @var null|string
     */
    public $metadata;

    /**
     * @var null|bool
     */
    public $enabled;

    /**
     * @var null|bool
     */
    public $ephemeral;

    /**
     * @var null|bool
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
