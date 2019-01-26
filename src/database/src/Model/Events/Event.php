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

abstract class Event
{
    protected $model;

    protected $method;

    public function __construct(Model $model, string $method)
    {
        $this->model = $model;
        $this->method = $method;
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

        return true;
    }
}
