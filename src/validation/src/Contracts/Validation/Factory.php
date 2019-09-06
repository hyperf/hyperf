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

namespace Hyperf\Validation\Contracts\Validation;

interface Factory
{
    /**
     * Create a new Validator instance.
     *
     * @return \Hyperf\Contract\ValidatorInterface
     */
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = []);

    /**
     * Register a custom validator extension.
     *
     * @param \Closure|string $extension
     * @param null|string $message
     */
    public function extend(string $rule, $extension, $message = null);

    /**
     * Register a custom implicit validator extension.
     *
     * @param \Closure|string $extension
     * @param null|string $message
     */
    public function extendImplicit(string $rule, $extension, $message = null);

    /**
     * Register a custom implicit validator message replacer.
     *
     * @param \Closure|string $replacer
     */
    public function replacer(string $rule, $replacer);
}
