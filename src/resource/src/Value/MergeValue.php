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
namespace Hyperf\Resource\Value;

use Hyperf\Collection\Collection;
use JsonSerializable;

class MergeValue
{
    /**
     * The data to be merged.
     */
    public array $data = [];

    /**
     * Create new merge value instance.
     */
    public function __construct(array|Collection|JsonSerializable $data)
    {
        if ($data instanceof Collection) {
            $this->data = $data->all();
        } elseif ($data instanceof JsonSerializable) {
            $this->data = $data->jsonSerialize();
        } else {
            $this->data = $data;
        }
    }
}
