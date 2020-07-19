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
     * @var string|null
     */
    public $serviceName;

    /**
     * @var string|null
     */
    public $groupName;

    /**
     * @var string|null
     */
    public $ip;

    /**
     * @var int|null
     */
    public $port;

    /**
     * @var string|null
     */
    public $clusterName;

    /**
     * @var string|null
     */
    public $namespaceId;

    /**
     * @var float|float|int|null
     */
    public $weight;

    /**
     * @var string|null
     */
    public $metadata;

    /**
     * @var bool|null
     */
    public $enabled;

    /**
     * @var bool|null
     */
    public $ephemeral;

    /**
     * @var bool|null
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
