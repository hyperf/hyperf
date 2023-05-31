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
namespace Hyperf\Translation {
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
}

namespace {
    if (! function_exists('__')) {
        /**
         * @deprecated since 3.1, please use \Hyperf\Translation\__ instead.
         */
        function __(string $key, array $replace = [], ?string $locale = null)
        {
            return \Hyperf\Translation\__($key, $replace, $locale);
        }
    }

    if (! function_exists('trans')) {
        /**
         * @deprecated since 3.1, please use \Hyperf\Translation\trans instead.
         */
        function trans(string $key, array $replace = [], ?string $locale = null)
        {
            return \Hyperf\Translation\__($key, $replace, $locale);
        }
    }

    if (! function_exists('trans_choice')) {
        /**
         * @deprecated since 3.1, please use \Hyperf\Translation\trans_choice instead.
         * @param mixed $number
         */
        function trans_choice(string $key, $number, array $replace = [], ?string $locale = null): string
        {
            return \Hyperf\Translation\trans_choice($key, $number, $replace, $locale);
        }
    }
}
