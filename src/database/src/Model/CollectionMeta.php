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

use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;

class CollectionMeta implements UnCompressInterface
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var array
     */
    public $keys;

    public function __construct(?string $class, array $keys = [])
    {
        $this->class = $class;
        $this->keys = $keys;
    }

    public function uncompress(): CompressInterface
    {
        if (is_null($this->class)) {
            return new Collection();
        }

        return $this->class::findMany($this->keys);
    }
}
