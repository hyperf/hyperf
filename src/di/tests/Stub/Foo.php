<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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
}
