<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Scout\Event;

use Hyperf\Database\Model\Collection;

class ModelsImported
{
    /**
     * The model collection.
     *
     * @var Collection
     */
    public $models;

    /**
     * Create a new event instance.
     */
    public function __construct(Collection $models)
    {
        $this->models = $models;
    }
}
