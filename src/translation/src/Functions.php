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

namespace Hyperf\Translation;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;

function __(string $key, array $replace = [], ?string $locale = null)
{
    $translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);
    return $translator->trans($key, $replace, $locale);
}

function trans(string $key, array $replace = [], ?string $locale = null)
{
    return __($key, $replace, $locale);
}

/**
 * @param mixed $number
 */
function trans_choice(string $key, $number, array $replace = [], ?string $locale = null): string
{
    $translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);
    return $translator->transChoice($key, $number, $replace, $locale);
}
