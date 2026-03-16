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

namespace HyperfTest\Di\Stub;

class Foo
{
    /**
     * @var string
     */
    public $string;

    /**
     * @var int
     */
    public $int;

    public function __construct(string $string = '', int $int = 1)
    {
        $this->string = $string;
        $this->int = $int;
    }

    public function getBar(?int $id, string $bar = 'testBar', array $ext = [], string $constants = BASE_PATH)
    {
        return [$id, $bar, $ext, $constants];
    }

    public function getFoo($id)
    {
        return [$id];
    }
}
