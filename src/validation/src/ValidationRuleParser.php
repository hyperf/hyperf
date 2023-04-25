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
use Hyperf\Collection\Arr;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Contract\Rule as RuleContract;
use Hyperf\Validation\Rules\Exists;
use Hyperf\Validation\Rules\Unique;
use stdClass;
use Stringable;

use function Hyperf\Collection\head;

class ValidationRuleParser
{
    /**
     * The implicit attributes.
     */
    public array $implicitAttributes = [];

    /**
     * Create a new validation rule parser.
     *
     * @param array $data the data being validated
     */
    public function __construct(public array $data)
    {
    }

    /**
     * Parse the human-friendly rules into a full rules array for the validator.
     *
     * @return stdClass
     */
    public function explode(array $rules)
    {
        $this->implicitAttributes = [];

        $rules = $this->explodeRules($rules);

        return (object) [
            'rules' => $rules,
            'implicitAttributes' => $this->implicitAttributes,
        ];
    }

    /**
     * Merge additional rules into a given attribute(s).
     *
     * @param array|string|Stringable $rules
     */
    public function mergeRules(array $results, array|string $attribute, mixed $rules = []): array
    {
        if (is_array($attribute)) {
            foreach ($attribute as $innerAttribute => $innerRules) {
                $results = $this->mergeRulesForAttribute($results, $innerAttribute, $innerRules);
            }

            return $results;
        }

        return $this->mergeRulesForAttribute(
            $results,
            $attribute,
            $rules
        );
    }

    /**
     * Extract the rule name and parameters from a rule.
     *
     * @param array|string $rules
     */
    public static function parse(mixed $rules): array
    {
        if ($rules instanceof RuleContract) {
            return [$rules, []];
        }

        if (is_array($rules)) {
            $rules = static::parseArrayRule($rules);
        } else {
            $rules = static::parseStringRule((string) $rules);
        }

        $rules[0] = static::normalizeRule($rules[0]);

        return $rules;
    }

    /**
     * Explode the rules into an array of explicit rules.
     */
    protected function explodeRules(array $rules): array
    {
        foreach ($rules as $key => $rule) {
            if (Str::contains((string) $key, '*')) {
                $rules = $this->explodeWildcardRules($rules, $key, [$rule]);

                unset($rules[$key]);
            } else {
                $rules[$key] = $this->explodeExplicitRule($rule);
            }
        }

        return $rules;
    }

    /**
     * Explode the explicit rule into an array if necessary.
     *
     * @param array|object|string $rule
     */
    protected function explodeExplicitRule($rule): array
    {
        if (is_string($rule)) {
            return explode('|', $rule);
        }
        if (is_object($rule)) {
            return [$this->prepareRule($rule)];
        }

        return array_map([$this, 'prepareRule'], $rule);
    }

    /**
     * Prepare the given rule for the Validator.
     *
     * @param mixed $rule
     * @return mixed
     */
    protected function prepareRule($rule)
    {
        if ($rule instanceof Closure) {
            $rule = new ClosureValidationRule($rule);
        }

        if (! is_object($rule)
            || $rule instanceof RuleContract
            || ($rule instanceof Exists && $rule->queryCallbacks())
            || ($rule instanceof Unique && $rule->queryCallbacks())) {
            return $rule;
        }

        return (string) $rule;
    }

    /**
     * Define a set of rules that apply to each element in an array attribute.
     *
     * @param array|string|Stringable $rules
     */
    protected function explodeWildcardRules(array $results, string $attribute, mixed $rules): array
    {
        $pattern = str_replace('\*', '[^\.]*', preg_quote($attribute));

        $data = ValidationData::initializeAndGatherData($attribute, $this->data);

        foreach ($data as $key => $value) {
            if (Str::startsWith($key, $attribute) || (bool) preg_match('/^' . $pattern . '\z/', $key)) {
                foreach ((array) $rules as $rule) {
                    $this->implicitAttributes[$attribute][] = $key;

                    $results = $this->mergeRules($results, $key, $rule);
                }
            }
        }

        return $results;
    }

    /**
     * Merge additional rules into a given attribute.
     *
     * @param array|string|Stringable $rules
     */
    protected function mergeRulesForAttribute(array $results, string $attribute, mixed $rules): array
    {
        $merge = head($this->explodeRules([$rules]));

        $results[$attribute] = array_merge(
            isset($results[$attribute]) ? $this->explodeExplicitRule($results[$attribute]) : [],
            $merge
        );

        return $results;
    }

    /**
     * Parse an array based rule.
     */
    protected static function parseArrayRule(array $rules): array
    {
        return [Str::studly(trim((string) Arr::get($rules, 0))), array_slice($rules, 1)];
    }

    /**
     * Parse a string based rule.
     */
    protected static function parseStringRule(string $rules): array
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Max:3" states that the value may only be three letters.
        if (str_contains($rules, ':')) {
            [$rules, $parameter] = explode(':', $rules, 2);

            $parameters = static::parseParameters($rules, $parameter);
        }

        return [Str::studly(trim($rules)), $parameters];
    }

    /**
     * Parse a parameter list.
     */
    protected static function parseParameters(string $rule, string $parameter): array
    {
        $rule = strtolower($rule);

        if (in_array($rule, ['regex', 'not_regex', 'notregex'], true)) {
            return [$parameter];
        }

        return str_getcsv($parameter);
    }

    /**
     * Normalizes a rule so that we can accept short types.
     */
    protected static function normalizeRule(string $rule): string
    {
        return match ($rule) {
            'Int' => 'Integer',
            'Bool' => 'Boolean',
            default => $rule,
        };
    }
}
