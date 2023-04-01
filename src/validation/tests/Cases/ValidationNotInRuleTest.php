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
use Hyperf\Validation\Rules\NotIn;
use PHPUnit\Framework\TestCase;

use function Hyperf\Collection\collect;

/**
 * @internal
 * @coversNothing
 */
class ValidationNotInRuleTest extends TestCase
{
    public function testItCorrectlyFormatsAStringVersionOfTheRule()
    {
        $rule = new NotIn(['Hyperf', 'Framework', 'PHP']);

        $this->assertEquals('not_in:"Hyperf","Framework","PHP"', (string) $rule);

        $rule = Rule::notIn([1, 2, 3, 4]);

        $this->assertEquals('not_in:"1","2","3","4"', (string) $rule);

        $rule = Rule::notIn(collect([1, 2, 3, 4]));

        $this->assertEquals('not_in:"1","2","3","4"', (string) $rule);

        $rule = Rule::notIn('1', '2', '3', '4');

        $this->assertEquals('not_in:"1","2","3","4"', (string) $rule);
    }
}
