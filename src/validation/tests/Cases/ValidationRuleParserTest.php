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
use Hyperf\Validation\Rule;
use Hyperf\Validation\ValidationRuleParser;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ValidationRuleParserTest extends TestCase
{
    public function testConditionalRulesWithDefault(): void
    {
        $isAdmin = true;

        $rules = ValidationRuleParser::filterConditionalRules([
            'name' => Rule::when($isAdmin, ['required', 'min:2'], ['string', 'max:10']),
            'email' => Rule::unless($isAdmin, ['required', 'min:2'], ['string', 'max:10']),
            'password' => Rule::unless($isAdmin, 'required|min:2', 'string|max:10'),
            'username' => ['required', Rule::when($isAdmin, ['min:2'], ['string', 'max:10'])],
            'address' => ['required', Rule::unless($isAdmin, ['min:2'], ['string', 'max:10'])],
        ]);

        $this->assertSame([
            'name' => ['required', 'min:2'],
            'email' => ['string', 'max:10'],
            'password' => ['string', 'max:10'],
            'username' => ['required', 'min:2'],
            'address' => ['required', 'string', 'max:10'],
        ], $rules);
    }

    public function testEmptyConditionalRulesArePreserved(): void
    {
        $isAdmin = true;

        $rules = ValidationRuleParser::filterConditionalRules([
            'name' => Rule::when($isAdmin, '', ['string', 'max:10']),
            'email' => Rule::unless($isAdmin, ['required', 'min:2']),
            'password' => Rule::unless($isAdmin, 'required|min:2', 'string|max:10'),
        ]);

        $this->assertEquals([
            'name' => [],
            'email' => [],
            'password' => ['string', 'max:10'],
        ], $rules);
    }

    public function testEmptyRulesArePreserved(): void
    {
        $isAdmin = true;

        $rules = ValidationRuleParser::filterConditionalRules([
            'name' => [],
            'email' => '',
            'password' => Rule::when($isAdmin, 'required|min:2'),
            'gender' => Rule::unless($isAdmin, 'required'),
        ]);

        $this->assertEquals([
            'name' => [],
            'email' => '',
            'password' => ['required', 'min:2'],
            'gender' => [],
        ], $rules);
    }

    public function testConditionalRulesAreProperlyExpandedAndFiltered(): void
    {
        $isAdmin = true;

        $rules = ValidationRuleParser::filterConditionalRules([
            'name' => Rule::when($isAdmin, ['required', 'min:2']),
            'email' => Rule::unless($isAdmin, ['required', 'min:2']),
            'password' => Rule::when($isAdmin, 'required|min:2'),
            'username' => ['required', Rule::when($isAdmin, ['min:2'])],
            'address' => ['required', Rule::unless($isAdmin, ['min:2'])],
            'city' => ['required', Rule::when(function (Fluent $input) {
                return true;
            }, ['min:2'])],
            'state' => ['required', Rule::when($isAdmin, function (Fluent $input) {
                return 'min:2';
            })],
            'zip' => ['required', Rule::when($isAdmin, function (Fluent $input) {
                return ['min:2'];
            })],
            'when_cb_true' => Rule::when(fn () => true, ['required'], ['nullable']),
            'when_cb_false' => Rule::when(fn () => false, ['required'], ['nullable']),
            'unless_cb_true' => Rule::unless(fn () => true, ['required'], ['nullable']),
            'unless_cb_false' => Rule::unless(fn () => false, ['required'], ['nullable']),
        ]);

        $this->assertEquals([
            'name' => ['required', 'min:2'],
            'email' => [],
            'password' => ['required', 'min:2'],
            'username' => ['required', 'min:2'],
            'address' => ['required'],
            'city' => ['required', 'min:2'],
            'state' => ['required', 'min:2'],
            'zip' => ['required', 'min:2'],
            'when_cb_true' => ['required'],
            'when_cb_false' => ['nullable'],
            'unless_cb_true' => ['nullable'],
            'unless_cb_false' => ['required'],
        ], $rules);
    }
}
