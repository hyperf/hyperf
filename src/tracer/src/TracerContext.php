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

namespace Hyperf\Tracer;

use Hyperf\Context\Context;
use OpenTracing\Span;
use OpenTracing\Tracer;

use function Hyperf\Support\make;

class TracerContext
{
    public const TRACER = 'tracer.tracer';

    public const ROOT = 'tracer.root';

    public const TRACE_ID = 'tracer.trace_id';

    public static function setTracer(Tracer $tracer): Tracer
    {
        return Context::set(self::TRACER, $tracer);
    }

    public static function getTracer(): Tracer
    {
        return Context::getOrSet(self::TRACER, fn () => make(Tracer::class));
    }

    public static function setRoot(Span $root): Span
    {
        return Context::set(self::ROOT, $root);
    }

    public static function getRoot(): ?Span
    {
        return Context::get(self::ROOT) ?: null;
    }

    public static function setTraceId(string $traceId): string
    {
        return Context::set(self::TRACE_ID, $traceId);
    }

    public static function getTraceId(): ?string
    {
        return Context::get(self::TRACE_ID) ?: null;
    }
}
