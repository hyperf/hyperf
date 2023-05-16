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
namespace Hyperf\Validation\Concerns;

use Hyperf\Collection\Arr;

trait ReplacesAttributes
{
    /**
     * Replace all place-holders for the between rule.
     */
    protected function replaceBetween(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace([':min', ':max'], $parameters, $message);
    }

    /**
     * Replace all place-holders for the date_format rule.
     */
    protected function replaceDateFormat(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':format', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the different rule.
     */
    protected function replaceDifferent(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceSame($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the digits rule.
     */
    protected function replaceDigits(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':digits', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the digits (between) rule.
     */
    protected function replaceDigitsBetween(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceBetween($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the min rule.
     */
    protected function replaceMin(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':min', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the max rule.
     */
    protected function replaceMax(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':max', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the in rule.
     */
    protected function replaceIn(string $message, string $attribute, string $rule, array $parameters): string
    {
        foreach ($parameters as &$parameter) {
            $parameter = $this->getDisplayableValue($attribute, $parameter);
        }

        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Replace all place-holders for the not_in rule.
     */
    protected function replaceNotIn(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceIn($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the in_array rule.
     */
    protected function replaceInArray(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':other', $this->getDisplayableAttribute($parameters[0]), $message);
    }

    /**
     * Replace all place-holders for the mimetypes rule.
     */
    protected function replaceMimetypes(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Replace all place-holders for the mimes rule.
     */
    protected function replaceMimes(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Replace all place-holders for the required_with rule.
     */
    protected function replaceRequiredWith(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':values', implode(' / ', $this->getAttributeList($parameters)), $message);
    }

    /**
     * Replace all place-holders for the required_with_all rule.
     */
    protected function replaceRequiredWithAll(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the required_without rule.
     */
    protected function replaceRequiredWithout(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the required_without_all rule.
     */
    protected function replaceRequiredWithoutAll(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the size rule.
     */
    protected function replaceSize(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':size', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the gt rule.
     */
    protected function replaceGt(string $message, string $attribute, string $rule, array $parameters): string
    {
        if (is_null($value = $this->getValue($parameters[0]))) {
            return str_replace(':value', $parameters[0], $message);
        }

        return str_replace(':value', (string) $this->getSize($attribute, $value), $message);
    }

    /**
     * Replace all place-holders for the lt rule.
     */
    protected function replaceLt(string $message, string $attribute, string $rule, array $parameters): string
    {
        if (is_null($value = $this->getValue($parameters[0]))) {
            return str_replace(':value', $parameters[0], $message);
        }

        return str_replace(':value', (string) $this->getSize($attribute, $value), $message);
    }

    /**
     * Replace all place-holders for the gte rule.
     */
    protected function replaceGte(string $message, string $attribute, string $rule, array $parameters): string
    {
        if (is_null($value = $this->getValue($parameters[0]))) {
            return str_replace(':value', $parameters[0], $message);
        }

        return str_replace(':value', (string) $this->getSize($attribute, $value), $message);
    }

    /**
     * Replace all place-holders for the lte rule.
     */
    protected function replaceLte(string $message, string $attribute, string $rule, array $parameters): string
    {
        if (is_null($value = $this->getValue($parameters[0]))) {
            return str_replace(':value', $parameters[0], $message);
        }

        return str_replace(':value', (string) $this->getSize($attribute, $value), $message);
    }

    /**
     * Replace all place-holders for the required_if rule.
     */
    protected function replaceRequiredIf(string $message, string $attribute, string $rule, array $parameters): string
    {
        $parameters[1] = $this->getDisplayableValue($parameters[0], Arr::get($this->data, $parameters[0]));

        $parameters[0] = $this->getDisplayableAttribute($parameters[0]);

        return str_replace([':other', ':value'], $parameters, $message);
    }

    /**
     * Replace all place-holders for the required_unless rule.
     */
    protected function replaceRequiredUnless(string $message, string $attribute, string $rule, array $parameters): string
    {
        $other = $this->getDisplayableAttribute($parameters[0]);

        $values = [];

        foreach (array_slice($parameters, 1) as $value) {
            $values[] = $this->getDisplayableValue($parameters[0], $value);
        }

        return str_replace([':other', ':values'], [$other, implode(', ', $values)], $message);
    }

    /**
     * Replace all place-holders for the same rule.
     */
    protected function replaceSame(string $message, string $attribute, string $rule, array $parameters): string
    {
        return str_replace(':other', $this->getDisplayableAttribute($parameters[0]), $message);
    }

    /**
     * Replace all place-holders for the before rule.
     */
    protected function replaceBefore(string $message, string $attribute, string $rule, array $parameters): string
    {
        if (! strtotime($parameters[0])) {
            return str_replace(':date', $this->getDisplayableAttribute($parameters[0]), $message);
        }

        return str_replace(':date', $this->getDisplayableValue($attribute, $parameters[0]), $message);
    }

    /**
     * Replace all place-holders for the before_or_equal rule.
     */
    protected function replaceBeforeOrEqual(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceBefore($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the after rule.
     */
    protected function replaceAfter(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceBefore($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the after_or_equal rule.
     */
    protected function replaceAfterOrEqual(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceBefore($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the date_equals rule.
     */
    protected function replaceDateEquals(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceBefore($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the dimensions rule.
     */
    protected function replaceDimensions(string $message, string $attribute, string $rule, array $parameters): string
    {
        $parameters = $this->parseNamedParameters($parameters);

        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $message = str_replace(':' . $key, $value, $message);
            }
        }

        return $message;
    }

    /**
     * Replace all place-holders for the ends_with rule.
     */
    protected function replaceEndsWith(string $message, string $attribute, string $rule, array $parameters): string
    {
        foreach ($parameters as &$parameter) {
            $parameter = $this->getDisplayableValue($attribute, $parameter);
        }

        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Replace all place-holders for the starts_with rule.
     */
    protected function replaceStartsWith(string $message, string $attribute, string $rule, array $parameters): string
    {
        foreach ($parameters as &$parameter) {
            $parameter = $this->getDisplayableValue($attribute, $parameter);
        }

        return str_replace(':values', implode(', ', $parameters), $message);
    }
}
