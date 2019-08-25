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

namespace Hyperf\Translation\Contract;

interface Translator
{
    /**
     * Get the translation for a given key.
     *
     * @param string $key
     * @param array $replace
     * @param null|string $locale
     * @return mixed
     */
    public function trans(string $key, array $replace = [], $locale = null);

    /**
     * Get a translation according to an integer value.
     *
     * @param string $key
     * @param array|\Countable|int $number
     * @param array $replace
     * @param null|string $locale
     * @return string
     */
    public function transChoice(string $key, $number, array $replace = [], $locale = null): string;

    /**
     * Get the default locale being used.
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * Set the default locale.
     *
     * @param string $locale
     */
    public function setLocale(string $locale);
}
