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
namespace Hyperf\Command\Event;

use Hyperf\Command\Command;
use Throwable;

class FailToHandle extends Event
{
    /**
     * @var Throwable
     */
    protected $throwable;

    public function __construct(Command $command, Throwable $throwable)
    {
        parent::__construct($command);

        $this->throwable = $throwable;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
