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
namespace Hyperf\Database\Model\Events;

use Hyperf\Database\Model\Model;
use Hyperf\Event\Stoppable;
use Psr\EventDispatcher\StoppableEventInterface;
use function class_basename;
use function lcfirst;
use function method_exists;

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
            $this->getModel()->{$this->getMethod()}($this);
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
