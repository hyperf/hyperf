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
namespace Hyperf\ConfigEtcd;

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
