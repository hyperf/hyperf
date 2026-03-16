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

namespace Hyperf\Validation\Contract;

use Closure;
use Hyperf\Contract\ValidatorInterface;

interface ValidatorFactoryInterface
{
    /**
     * Create a new Validator instance.
     */
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = []): ValidatorInterface;

    /**
     * Register a custom validator extension.
     */
    public function extend(string $rule, Closure|string $extension, ?string $message = null);

    /**
     * Register a custom implicit validator extension.
     */
    public function extendImplicit(string $rule, Closure|string $extension, ?string $message = null);

    /**
     * Register a custom implicit validator message replacer.
     */
    public function replacer(string $rule, Closure|string $replacer);
}
