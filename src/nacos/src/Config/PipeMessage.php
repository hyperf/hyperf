<?php

declare(strict_types = 1);

namespace Hyperf\Nacos\Config;

class PipeMessage
{
    /**
     * @var array
     */
    public $configurations;

    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }
}
