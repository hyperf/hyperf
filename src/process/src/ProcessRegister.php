<?php

namespace Hyperf\Process;

use Hyperf\Contract\ProcessInterface;

class ProcessRegister
{

    /**
     * @var array
     */
    protected static $processes = [];

    public static function register(ProcessInterface $process): void
    {
        static::$processes[] = $process;
    }

    public static function all(): array
    {
        return static::$processes;
    }

    public static function clear(): void
    {
        static::$processes = [];
    }

}