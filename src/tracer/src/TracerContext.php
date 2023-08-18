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

class TracerContext
{
    public const TRACER = 'tracer.tracer';

    public const ROOT = 'tracer.root';

    public static function setTracer(Tracer $tracer): Tracer
    {
        return Context::set(self::TRACER, $tracer);
    }

    public static function getTracer(): ?Tracer
    {
        return Context::get(self::TRACER) ?: null;
    }

    public static function setRoot(Span $root): Span
    {
        return Context::set(self::ROOT, $root);
    }

    public static function getRoot(): ?Span
    {
        return Context::get(self::ROOT) ?: null;
    }
}
