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

namespace HyperfTest\Validation\Cases;

use Hyperf\Support\Fluent;
use Hyperf\Validation\ConditionalRules;
use Hyperf\Validation\Contract\Rule as RuleContract;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ValidationConditionalRulesTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testPassesWhenConditionIsTrue()
    {
        $condition = true;
        $rules = 'required|string';
        $defaultRules = 'nullable';
        $data = ['field' => 'value'];

        $conditionalRules = new ConditionalRules($condition, $rules, $defaultRules);

        $this->assertTrue($conditionalRules->passes($data));
    }

    public function testPassesWhenConditionIsCallableAndReturnsTrue()
    {
        $condition = function (Fluent $data) {
            return isset($data->field);
        };
        $rules = 'required|string';
        $defaultRules = 'nullable';
        $data = ['field' => 'value'];

        $conditionalRules = new ConditionalRules($condition, $rules, $defaultRules);

        $this->assertTrue($conditionalRules->passes($data));
    }

    public function testPassesWhenConditionIsCallableAndReturnsFalse()
    {
        $condition = function (Fluent $data) {
            return isset($data->field);
        };
        $rules = 'required|string';
        $defaultRules = 'nullable';
        $data = [];

        $conditionalRules = new ConditionalRules($condition, $rules, $defaultRules);

        $this->assertFalse($conditionalRules->passes($data));
    }

    public function testGetRulesWhenRulesIsString()
    {
        $condition = true;
        $rules = 'required|string';
        $defaultRules = 'nullable';
        $data = [];

        $conditionalRules = new ConditionalRules($condition, $rules, $defaultRules);

        $this->assertEquals(['required', 'string'], $conditionalRules->rules($data));
    }

    public function testGetRulesWhenRulesIsClosure()
    {
        $condition = true;
        $rules = m::mock(RuleContract::class);
        $rules->shouldReceive('passes')->andReturn(true);
        $defaultRules = 'nullable';
        $data = [];

        $conditionalRules = new ConditionalRules($condition, $rules, $defaultRules);

        $this->assertEquals($rules, $conditionalRules->rules($data));
    }

    public function testGetDefaultRulesWhenRulesIsString()
    {
        $condition = true;
        $rules = 'required|string';
        $defaultRules = 'nullable';
        $data = [];

        $conditionalRules = new ConditionalRules($condition, $rules, $defaultRules);

        $this->assertEquals(['nullable'], $conditionalRules->defaultRules($data));
    }

    public function testGetDefaultRulesWhenRulesIsClosure()
    {
        $condition = true;
        $rules = 'required|string';
        $defaultRules = m::mock(RuleContract::class);
        $defaultRules->shouldReceive('passes')->andReturn(true);
        $data = [];

        $conditionalRules = new ConditionalRules($condition, $rules, $defaultRules);

        $this->assertEquals($defaultRules, $conditionalRules->defaultRules($data));
    }
}
