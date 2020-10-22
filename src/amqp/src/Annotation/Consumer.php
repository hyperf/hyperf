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
namespace Hyperf\Amqp\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Consumer extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $exchange = '';

    /**
     * @var string
     */
    public $routingKey = '';

    /**
     * @var string
     */
    public $queue = '';

    /**
     * @var string
     */
    public $name = 'Consumer';

    /**
     * @var int
     */
    public $nums = 1;

    /**
     * @var null|bool
     */
    public $enable;

    /**
     * @var int
     */
    public $maxConsumption = 0;
}
