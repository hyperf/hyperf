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
namespace Hyperf\Contract;

use Hyperf\Utils\Contracts\MessageBag;
use Hyperf\Utils\Contracts\MessageProvider;

interface ValidatorInterface extends MessageProvider
{
    /**
     * Run the validator's rules against its data.
     */
    public function validate(): array;

    /**
     * Get the attributes and values that were validated.
     */
    public function validated(): array;

    /**
     * Determine if the data fails the validation rules.
     */
    public function fails(): bool;

    /**
     * Get the failed validation rules.
     */
    public function failed(): array;

    /**
     * Add conditions to a given field based on a Closure.
     *
     * @param array|string $attribute
     * @param array|string $rules
     * @return $this
     */
    public function sometimes($attribute, $rules, callable $callback);

    /**
     * Add an after validation callback.
     *
     * @param callable|string $callback
     * @return $this
     */
    public function after($callback);

    /**
     * Get all of the validation error messages.
     */
    public function errors(): MessageBag;
}
