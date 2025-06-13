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

use Hyperf\Validation\Rules\RequiredIf;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ValidationRequiredIfTest extends TestCase
{
    public function testItClousureReturnsFormatsAStringVersionOfTheRule()
    {
        $rule = new RequiredIf(function () {
            return true;
        });

        $this->assertEquals('required', (string) $rule);

        $rule = new RequiredIf(function () {
            return false;
        });

        $this->assertEquals('', (string) $rule);

        $rule = new RequiredIf(true);

        $this->assertEquals('required', (string) $rule);

        $rule = new RequiredIf(false);

        $this->assertEquals('', (string) $rule);
    }
}
