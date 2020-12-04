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
namespace Hyperf\Waiter;

use Closure;
use Hyperf\Engine\Channel;
use Hyperf\Utils\Coroutine;
use Throwable;

class Waiter
{
    /**
     * @var float
     */
    protected $pushTimeout = 10.0;

    /**
     * @var float
     */
    protected $popTimeout = 10.0;

    public function __construct(float $pushTimeout = 10.0, float $popTimeout = 10.0)
    {
        $this->pushTimeout = $pushTimeout;
        $this->popTimeout = $popTimeout;
    }

    public function wait(Closure $closure)
    {
        $channel = new Channel(1);
        Coroutine::create(function () use ($channel, $closure) {
            try {
                $result = $closure();
            } catch (Throwable $exception) {
                $result = new ExceptionThrower($exception);
            } finally {
                $channel->push($result, $this->pushTimeout);
            }
        });

        $result = $channel->pop($this->popTimeout);
        if ($result instanceof ExceptionThrower) {
            throw $result->getThrowable();
        }

        return $result;
    }
}
