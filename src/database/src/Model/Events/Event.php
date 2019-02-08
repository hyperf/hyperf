<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Model\Events;

use function class_basename;
use Hyperf\Database\Model\Model;
use Hyperf\Event\Stoppable;
use function lcfirst;
use function method_exists;
use Psr\EventDispatcher\StoppableEventInterface;

abstract class Event implements StoppableEventInterface
{
    use Stoppable;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var null|string
     */
    protected $method;

    public function __construct(Model $model, ?string $method = null)
    {
        $this->model = $model;
        $this->method = $method ?? lcfirst(class_basename(static::class));
    }

    public function handle()
    {
        if (method_exists($this->getModel(), $this->getMethod())) {
            return $this->getModel()->{$this->getMethod()}($this);
        }

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
