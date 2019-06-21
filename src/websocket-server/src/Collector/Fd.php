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

namespace Hyperf\WebSocketServer\Collector;

class Fd
{
    /**
     * @var int
     */
    public $fd;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $method;

    public function __construct(int $fd, string $class, string $method)
    {
        $this->fd = $fd;
        $this->class = $class;
        $this->method = $method;
    }
}
