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
namespace Hyperf\ModelCache\Listener;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\Utils\Traits\StaticInstance;

class WaitToDeleteModels
{
    use StaticInstance;

    /**
     * @var CacheableInterface[]
     */
    protected $models;

    public function add(CacheableInterface $model)
    {
        $this->models[] = $model;
    }

    public function getModels(): array
    {
        return $this->models;
    }
}
