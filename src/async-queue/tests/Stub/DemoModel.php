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
use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;

class DemoModel implements CompressInterface
{
    public $id;

    public $name;

    public $gendar;

    public $signature;

    public function __construct($id, $name, $gendar, $signature)
    {
        $this->id = $id;
        $this->name = $name;
        $this->gendar = $gendar;
        $this->signature = $signature;
    }

    public function compress(): UnCompressInterface
    {
        Context::set('test.async-queue.demo.model.' . $this->id, [
            $this->name, $this->gendar, $this->signature,
        ]);

        return new DemoModelMeta($this->id);
    }
}
