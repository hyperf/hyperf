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

class ServiceModel extends AbstractModel
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
    public $namespaceId;

    /**
     * Between 0 to 1.
     * @var float
     */
    public $protectThreshold = 0.0;

    /**
     * @var string
     */
    public $metadata;

    /**
     * A JSON string.
     *
     * @var string
     */
    public $selector;

    /**
     * @var string[]
     */
    public $requiredFields = ['serviceName'];
}
