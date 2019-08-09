<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Model;

use Hyperf\Contract\CodeDegenerateInterface;
use Hyperf\Contract\CodeGenerateInterface;

class CollectionMeta implements CodeDegenerateInterface
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

    public function degenerate(): CodeGenerateInterface
    {
        if (is_null($this->class)) {
            return new Collection();
        }

        return $this->class::findMany($this->keys);
    }
}
