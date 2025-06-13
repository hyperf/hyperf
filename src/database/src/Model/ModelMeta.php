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

namespace Hyperf\Database\Model;

use Hyperf\Contract\UnCompressInterface;

class ModelMeta implements UnCompressInterface
{
    /**
     * @var int|string
     */
    public $key;

    /**
     * @param int|string $key
     */
    public function __construct(public string $class, $key)
    {
        $this->key = $key;
    }

    public function uncompress()
    {
        if (is_null($this->key)) {
            return new $this->class();
        }
        return $this->class::find($this->key);
    }
}
