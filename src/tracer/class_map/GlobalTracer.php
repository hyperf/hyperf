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

namespace OpenTracing;

use Hyperf\Tracer\TracerContext;

final class GlobalTracer
{
    /**
     * @var Tracer
     */
    private static $instance;

    /**
     * @var bool
     */
    private static $isRegistered = false;

    /**
     * GlobalTracer::set sets the [singleton] Tracer returned by get().
     * Those who use GlobalTracer (rather than directly manage a Tracer instance)
     * should call GlobalTracer::set as early as possible in bootstrap, prior to
     * start a new span. Prior to calling GlobalTracer::set, any Spans started
     * via the `Tracer::startActiveSpan` (etc) globals are noops.
     */
    public static function set(Tracer $tracer): void
    {
        TracerContext::setTracer($tracer);
        self::$isRegistered = true;
    }

    /**
     * GlobalTracer::get returns the global singleton `Tracer` implementation.
     * Before `GlobalTracer::set` is called, the `GlobalTracer::get` is a noop
     * implementation that drops all data handed to it.
     */
    public static function get(): Tracer
    {
        return TracerContext::getTracer();
    }

    /**
     * Returns true if a global tracer has been registered, otherwise returns false.
     */
    public static function isRegistered(): bool
    {
        return self::$isRegistered;
    }
}
