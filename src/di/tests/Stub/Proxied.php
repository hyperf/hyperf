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

class Proxied
{
    public $id;

    public $name;

    public static $isInitialized = false;

    public function __construct(string $id, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
        self::$isInitialized = true;
    }

    public function setId(string $a)
    {
        return $this->id = $a;
    }

    public function getId()
    {
        return $this->id;
    }
}
