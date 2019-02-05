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

use Hyperf\Database\Model\Model;
use function lcfirst;
use function class_basename;
use function method_exists;

abstract class Event
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string|null
     */
    protected $method;

    public function __construct(Model $model, ?string $method = null)
    {
        $this->model = $model;
        $this->method = $method ?? lcfirst(class_basename(static::class));
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function handle()
    {
        $model = $this->getModel();
        $method = $this->getMethod();
        if ($model && method_exists($model, $method)) {
            return $model->{$method}();
        }

        return $this;
    }
}
