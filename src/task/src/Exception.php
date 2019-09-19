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

namespace Hyperf\Task;

use Throwable;

class Exception
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var int
     */
    public $code;

    /**
     * @var string
     */
    public $message;

    public function __construct(Throwable $throwable)
    {
        $this->class = get_class($throwable);
        $this->code = $throwable->getCode();
        $this->message = $throwable->getMessage();
    }
}
