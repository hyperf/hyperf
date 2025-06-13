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

use Hyperf\Validation\Rule;
use Hyperf\Validation\Rules\Dimensions;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ValidationDimensionsRuleTest extends TestCase
{
    public function testItCorrectlyFormatsAStringVersionOfTheRule()
    {
        $rule = new Dimensions(['min_width' => 100, 'min_height' => 100]);

        $this->assertEquals('dimensions:min_width=100,min_height=100', (string) $rule);

        $rule = Rule::dimensions()->width(200)->height(100);

        $this->assertEquals('dimensions:width=200,height=100', (string) $rule);

        $rule = Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2);

        $this->assertEquals('dimensions:max_width=1000,max_height=500,ratio=1.5', (string) $rule);

        $rule = new Dimensions(['ratio' => '2/3']);

        $this->assertEquals('dimensions:ratio=2/3', (string) $rule);

        $rule = Rule::dimensions()->minWidth(300)->minHeight(400);

        $this->assertEquals('dimensions:min_width=300,min_height=400', (string) $rule);
    }
}
