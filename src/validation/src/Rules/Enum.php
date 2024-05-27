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

namespace Hyperf\Validation\Rules;

use Hyperf\Collection\Arr;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Validation\Contract\Rule;
use Hyperf\Validation\Contract\ValidatorAwareRule;
use Hyperf\Validation\Validator;
use TypeError;
use UnitEnum;

class Enum implements Rule, ValidatorAwareRule
{
    use Conditionable;

    /**
     * The validator instance.
     */
    protected Validator $validator;

    /**
     * The cases that should be considered valid.
     */
    protected array $only = [];

    /**
     * The cases that should be considered invalid.
     */
    protected array $except = [];

    /**
     * Create a new enum rule instance.
     * @param string $type The type of the enum
     */
    public function __construct(
        protected string $type,
    ) {
    }

    public function passes(string $attribute, mixed $value): bool
    {
        if ($value instanceof $this->type) {
            return $this->isDesirable($value);
        }

        if (is_null($value) || ! enum_exists($this->type) || ! method_exists($this->type, 'tryFrom')) {
            return false;
        }

        try {
            $value = $this->type::tryFrom($value);
            return ! is_null($value) && $this->isDesirable($value);
        } catch (TypeError) {
            return false;
        }
    }

    /**
     * Specify the cases that should be considered valid.
     */
    public function only(array|UnitEnum $values): static
    {
        $this->only = Arr::wrap($values);

        return $this;
    }

    /**
     * Specify the cases that should be considered invalid.
     */
    public function except(array|UnitEnum $values): static
    {
        $this->except = Arr::wrap($values);

        return $this;
    }

    public function message(): array|string
    {
        $message = $this->validator->getTranslator()->get('validation.enum');

        return $message === 'validation.enum'
            ? ['The selected :attribute is invalid.']
            : $message;
    }

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Determine if the given case is a valid case based on the only / except values.
     */
    protected function isDesirable(mixed $value): bool
    {
        return match (true) {
            ! empty($this->only) => in_array(needle: $value, haystack: $this->only, strict: true),
            ! empty($this->except) => ! in_array(needle: $value, haystack: $this->except, strict: true),
            default => true,
        };
    }
}
