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

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\StrCache;
use Hyperf\Validation\Validator;

use function Hyperf\Support\make;

trait FormatsMessages
{
    use ReplacesAttributes;

    /**
     * Replace all error message place-holders with actual values.
     */
    public function makeReplacements(string $message, string $attribute, string $rule, array $parameters): string
    {
        $message = $this->replaceAttributePlaceholder(
            $message,
            $this->getDisplayableAttribute($attribute)
        );

        $message = $this->replaceInputPlaceholder($message, $attribute);

        if (isset($this->replacers[StrCache::snake($rule)])) {
            return $this->callReplacer($message, $attribute, StrCache::snake($rule), $parameters, $this);
        }
        if (method_exists($this, $replacer = "replace{$rule}")) {
            return $this->{$replacer}($message, $attribute, $rule, $parameters);
        }

        return $message;
    }

    /**
     * Get the displayable name of the attribute.
     */
    public function getDisplayableAttribute(string $attribute): string
    {
        $primaryAttribute = $this->getPrimaryAttribute($attribute);

        $expectedAttributes = $attribute != $primaryAttribute
            ? [$attribute, $primaryAttribute] : [$attribute];

        foreach ($expectedAttributes as $name) {
            // The developer may dynamically specify the array of custom attributes on this
            // validator instance. If the attribute exists in this array it is used over
            // the other ways of pulling the attribute name for this given attributes.
            if (isset($this->customAttributes[$name])) {
                return $this->customAttributes[$name];
            }

            // We allow for a developer to specify language lines for any attribute in this
            // application, which allows flexibility for displaying a unique displayable
            // version of the attribute name instead of the name used in an HTTP POST.
            if ($line = $this->getAttributeFromTranslations($name)) {
                return $line;
            }
        }

        // When no language line has been specified for the attribute and it is also
        // an implicit attribute we will display the raw attribute's name and not
        // modify it with any of these replacements before we display the name.
        if (isset($this->implicitAttributes[$primaryAttribute])) {
            return $attribute;
        }

        return str_replace('_', ' ', StrCache::snake($attribute));
    }

    /**
     * Get the displayable name of the value.
     *
     * @param mixed $value
     */
    public function getDisplayableValue(string $attribute, $value): string
    {
        if (isset($this->customValues[$attribute][$value])) {
            return $this->customValues[$attribute][$value];
        }

        if (is_array($value)) {
            return 'array';
        }

        $key = "validation.values.{$attribute}.{$value}";

        if (($line = $this->translator->trans($key)) !== $key) {
            return $line;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'empty';
        }

        return (string) $value;
    }

    /**
     * Get the validation message for an attribute and rule.
     */
    protected function getMessage(string $attribute, string $rule): string
    {
        $inlineMessage = $this->getInlineMessage($attribute, $rule);

        // First we will retrieve the custom message for the validation rule if one
        // exists. If a custom validation message is being used we'll return the
        // custom message, otherwise we'll keep searching for a valid message.
        if (! is_null($inlineMessage)) {
            return $inlineMessage;
        }

        $lowerRule = StrCache::snake($rule);

        $customMessage = $this->getCustomMessageFromTranslator(
            $customKey = "validation.custom.{$attribute}.{$lowerRule}"
        );

        // First we check for a custom defined validation message for the attribute
        // and rule. This allows the developer to specify specific messages for
        // only some attributes and rules that need to get specially formed.
        if ($customMessage !== $customKey) {
            return $customMessage;
        }

        // If the rule being validated is a "size" rule, we will need to gather the
        // specific error message for the type of attribute being validated such
        // as a number, file or string which all have different message types.
        if (in_array($rule, $this->sizeRules)) {
            return $this->getSizeMessage($attribute, $rule);
        }

        // Finally, if no developer specified messages have been set, and no other
        // special messages apply for this rule, we will just pull the default
        // messages out of the translator service for this validation rule.
        $key = "validation.{$lowerRule}";

        if ($key != ($value = $this->translator->trans($key))) {
            return $value;
        }

        return $this->getFromLocalArray(
            $attribute,
            $lowerRule,
            $this->fallbackMessages
        ) ?: $key;
    }

    /**
     * Get the proper inline error message for standard and size rules.
     *
     * @return null|string
     */
    protected function getInlineMessage(string $attribute, string $rule)
    {
        $inlineEntry = $this->getFromLocalArray($attribute, StrCache::snake($rule));

        return is_array($inlineEntry) && in_array($rule, $this->sizeRules)
            ? $inlineEntry[$this->getAttributeType($attribute)]
            : $inlineEntry;
    }

    /**
     * Get the inline message for a rule if it exists.
     *
     * @param null|array $source
     * @return null|string
     */
    protected function getFromLocalArray(string $attribute, string $lowerRule, $source = null)
    {
        $source = $source ?: $this->customMessages;

        $keys = ["{$attribute}.{$lowerRule}", $lowerRule];

        // First we will check for a custom message for an attribute specific rule
        // message for the fields, then we will check for a general custom line
        // that is not attribute specific. If we find either we'll return it.
        foreach ($keys as $key) {
            foreach (array_keys($source) as $sourceKey) {
                if (Str::is($sourceKey, $key)) {
                    return $source[$sourceKey];
                }
            }
        }
    }

    /**
     * Get the custom error message from translator.
     */
    protected function getCustomMessageFromTranslator(string $key): string
    {
        if (($message = $this->translator->trans($key)) !== $key) {
            return $message;
        }

        // If an exact match was not found for the key, we will collapse all of these
        // messages and loop through them and try to find a wildcard match for the
        // given key. Otherwise, we will simply return the key's value back out.
        $shortKey = preg_replace(
            '/^validation\.custom\./',
            '',
            $key
        );

        return $this->getWildcardCustomMessages(Arr::dot(
            (array) $this->translator->trans('validation.custom')
        ), $shortKey, $key);
    }

    /**
     * Check the given messages for a wildcard key.
     */
    protected function getWildcardCustomMessages(array $messages, string $search, string $default): string
    {
        foreach ($messages as $key => $message) {
            if ($search === $key || (Str::contains((string) $key, ['*']) && Str::is($key, $search))) {
                return $message;
            }
        }

        return $default;
    }

    /**
     * Get the proper error message for an attribute and size rule.
     */
    protected function getSizeMessage(string $attribute, string $rule): string
    {
        $lowerRule = StrCache::snake($rule);

        // There are three different types of size validations. The attribute may be
        // either a number, file, or string so we will check a few things to know
        // which type of value it is and return the correct line for that type.
        $type = $this->getAttributeType($attribute);

        $key = "validation.{$lowerRule}.{$type}";

        return $this->translator->trans($key);
    }

    /**
     * Get the data type of the given attribute.
     */
    protected function getAttributeType(string $attribute): string
    {
        // We assume that the attributes present in the file array are files so that
        // means that if the attribute does not have a numeric rule and the files
        // list doesn't have it we'll just consider it a string by elimination.
        if ($this->hasRule($attribute, $this->numericRules)) {
            return 'numeric';
        }
        if ($this->hasRule($attribute, ['Array'])) {
            return 'array';
        }
        if ($this->getValue($attribute) instanceof UploadedFile) {
            return 'file';
        }

        return 'string';
    }

    /**
     * Get the given attribute from the attribute translations.
     */
    protected function getAttributeFromTranslations(string $name): string
    {
        return (string) Arr::get($this->translator->trans('validation.attributes'), $name);
    }

    /**
     * Replace the :attribute placeholder in the given message.
     */
    protected function replaceAttributePlaceholder(string $message, string $value): string
    {
        return str_replace(
            [':attribute', ':ATTRIBUTE', ':Attribute'],
            [$value, Str::upper($value), Str::ucfirst($value)],
            $message
        );
    }

    /**
     * Replace the :input placeholder in the given message.
     */
    protected function replaceInputPlaceholder(string $message, string $attribute): string
    {
        $actualValue = $this->getValue($attribute);

        if (is_scalar($actualValue) || is_null($actualValue)) {
            $message = str_replace(':input', $this->getDisplayableValue($attribute, $actualValue), $message);
        }

        return $message;
    }

    /**
     * Transform an array of attributes to their displayable form.
     */
    protected function getAttributeList(array $values): array
    {
        $attributes = [];

        // For each attribute in the list we will simply get its displayable form as
        // this is convenient when replacing lists of parameters like some of the
        // replacement functions do when formatting out the validation message.
        foreach ($values as $key => $value) {
            $attributes[$key] = $this->getDisplayableAttribute($value);
        }

        return $attributes;
    }

    /**
     * Call a custom validator message replacer.
     */
    protected function callReplacer(string $message, string $attribute, string $rule, array $parameters, Validator $validator): ?string
    {
        $callback = $this->replacers[$rule];

        if ($callback instanceof Closure) {
            return call_user_func_array($callback, func_get_args());
        }
        if (is_string($callback)) {
            return $this->callClassBasedReplacer($callback, $message, $attribute, $rule, $parameters, $validator);
        }
    }

    /**
     * Call a class based validator message replacer.
     *
     * @param Validator $validator
     */
    protected function callClassBasedReplacer(string $callback, string $message, string $attribute, string $rule, array $parameters, $validator): string
    {
        [$class, $method] = Str::parseCallback($callback, 'replace');

        return call_user_func_array([make($class), $method], array_slice(func_get_args(), 1));
    }
}
