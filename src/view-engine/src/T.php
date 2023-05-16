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
namespace Hyperf\ViewEngine;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\ViewEngine\Contract\DeferringDisplayableValue;
use Hyperf\ViewEngine\Contract\Htmlable;

class T
{
    /**
     * Encode HTML special characters in a string.
     *
     * @param DeferringDisplayableValue|Htmlable|string $value
     * @param bool $doubleEncode
     * @return string
     */
    public static function e($value, $doubleEncode = true)
    {
        if ($value instanceof DeferringDisplayableValue) {
            $value = $value->resolveDisplayableValue();
        }

        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }

    public static function inject($name)
    {
        return ApplicationContext::getContainer()
            ->get($name);
    }

    public static function translator()
    {
        return ApplicationContext::getContainer()
            ->get(TranslatorInterface::class);
    }
}
