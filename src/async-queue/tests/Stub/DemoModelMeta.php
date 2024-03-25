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

namespace HyperfTest\AsyncQueue\Stub;

use Hyperf\Context\Context;
use Hyperf\Contract\UnCompressInterface;

class DemoModelMeta implements UnCompressInterface
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function uncompress()
    {
        $data = Context::get('test.async-queue.demo.model.' . $this->id);

        if ($this->id === 9999) {
            return null;
        }

        return new DemoModel($this->id, ...$data);
    }
}
