<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Crontab;

class PipeMessage
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var callable
     */
    public $callable;

    /**
     * @var array
     */
    public $data;

    /**
     * PipeMessage constructor.
     * @param $type
     * @param $callable
     * @param array $data
     */
    public function __construct($type, $callable, array $data)
    {
        $this->type = $type;
        $this->callable = $callable;
        $this->data = $data;
    }
}
