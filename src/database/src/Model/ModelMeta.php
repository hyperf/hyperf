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

class ModelMeta implements CodeDegenerateInterface
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var int|string
     */
    public $key;

    /**
     * @param int|string $key
     */
    public function __construct(string $class, $key)
    {
        $this->class = $class;
        $this->key = $key;
    }

    public function degenerate(): CodeGenerateInterface
    {
        if (is_null($this->key)) {
            return new $this->class();
        }
        return $this->class::find($this->key);
    }
}
