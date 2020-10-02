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

use Hyperf\Validation\Contract\Rule;
use Hyperf\Validation\Contract\Rule as RuleContract;

class ClosureValidationRule implements RuleContract
{
    /**
     * The callback that validates the attribute.
     *
     * @var \Closure
     */
    public $callback;

    /**
     * Indicates if the validation callback failed.
     *
     * @var bool
     */
    public $failed = false;

    /**
     * The validation error message.
     *
     * @var null|string
     */
    public $message;

    /**
     * Create a new Closure based validation rule.
     *
     * @param \Closure $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     */
    public function passes(string $attribute, $value): bool
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
