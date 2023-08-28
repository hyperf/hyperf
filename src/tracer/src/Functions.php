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

use function bin2hex;
use function ctype_xdigit;
use function openssl_random_pseudo_bytes;
use function strlen;

function generateTraceIdWith128bits(): string
{
    return bin2hex(openssl_random_pseudo_bytes(16));
}

function generateNextId(): string
{
    return bin2hex(openssl_random_pseudo_bytes(8));
}

function isValidTraceId(string $value): bool
{
    return ctype_xdigit($value)
        && strlen($value) > 0 && strlen($value) <= 32;
}

function isValidSpanId(string $value): bool
{
    return ctype_xdigit($value)
        && strlen($value) > 0 && strlen($value) <= 16;
}
