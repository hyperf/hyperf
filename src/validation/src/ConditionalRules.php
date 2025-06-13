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
use Hyperf\Support\Fluent;
use Hyperf\Validation\Contract\Rule as RuleContract;

use function Hyperf\Support\value;

class ConditionalRules
{
    public function __construct(
        protected bool|Closure $condition,
        protected array|Closure|RuleContract|string $rules,
        protected array|Closure|RuleContract|string $defaultRules,
    ) {
    }

    /**
     * Determine if the conditional rules should be added.
     */
    public function passes(array $data = []): bool
    {
        return is_callable($this->condition)
            ? call_user_func($this->condition, new Fluent($data))
            : $this->condition;
    }

    /**
     * Get the rules.
     */
    public function rules(array $data = [])
    {
        return is_string($this->rules)
            ? explode('|', $this->rules)
            : value($this->rules, new Fluent($data));
    }

    /**
     * Get the default rules.
     *
     * @return array
     */
    public function defaultRules(array $data = [])
    {
        return is_string($this->defaultRules)
            ? explode('|', $this->defaultRules)
            : value($this->defaultRules, new Fluent($data));
    }
}
