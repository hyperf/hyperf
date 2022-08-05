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
    public static $isInitialized = false;

    public function __construct(public string $id, public $name = null)
    {
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
