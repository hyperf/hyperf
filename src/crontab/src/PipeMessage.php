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
namespace Hyperf\Crontab;

class PipeMessage
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var array|callable
     */
    public $callable;

    /**
     * @var \Hyperf\Crontab\Crontab
     */
    public $data;

    public function __construct(string $type, $callable, Crontab $data)
    {
        $this->type = $type;
        $this->callable = $callable;
        $this->data = $data;
    }
}
