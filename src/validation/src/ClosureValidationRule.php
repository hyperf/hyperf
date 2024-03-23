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

namespace Hyperf\Validation;

use Closure;
use Hyperf\Validation\Contract\Rule;
use Hyperf\Validation\Contract\Rule as RuleContract;

class ClosureValidationRule implements RuleContract
{
    /**
     * Indicates if the validation callback failed.
     */
    public bool $failed = false;

    /**
     * The validation error message.
     */
    public ?string $message = null;

    /**
     * Create a new Closure based validation rule.
     *
     * @param Closure $callback the callback that validates the attribute
     */
    public function __construct(public Closure $callback)
    {
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes(string $attribute, mixed $value): bool
    {
        $this->failed = false;

        $this->callback->__invoke($attribute, $value, function ($message) {
            $this->failed = true;

            $this->message = $message;
        });

        return ! $this->failed;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->message;
    }
}
