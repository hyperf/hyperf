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
namespace Hyperf\ModelCache;

use Hyperf\Support\Traits\StaticInstance;

class InvalidCacheManager
{
    use StaticInstance;

    /**
     * @var CacheableInterface[]
     */
    protected array $models = [];

    public function push(CacheableInterface $model): void
    {
        $this->models[] = $model;
    }

    public function delete(): void
    {
        while ($model = array_pop($this->models)) {
            $model->deleteCache();
        }
    }
}
