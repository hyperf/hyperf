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
namespace Hyperf\Task;

use Hyperf\Serializer\ExceptionNormalizer;
use Psr\Container\ContainerInterface;
use Throwable;

class Exception
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var array|bool|float|int|string
     */
    public $attributes;

    public function __construct(ContainerInterface $container, Throwable $throwable)
    {
        $this->class = get_class($throwable);
        $this->attributes = $container->get(ExceptionNormalizer::class)->normalize($throwable);
    }
}
