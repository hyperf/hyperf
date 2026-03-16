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

use Exception;
use Hyperf\Validation\Rules\ExcludeIf;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class ValidationExcludeIfTest extends TestCase
{
    public function testItReturnsStringVersionOfRuleWhenCast()
    {
        $rule = new ExcludeIf(function () {
            return true;
        });

        $this->assertSame('exclude', (string) $rule);

        $rule = new ExcludeIf(function () {
            return false;
        });

        $this->assertSame('', (string) $rule);

        $rule = new ExcludeIf(true);

        $this->assertSame('exclude', (string) $rule);

        $rule = new ExcludeIf(false);

        $this->assertSame('', (string) $rule);
    }

    public function testItValidatesCallableAndBooleanAreAcceptableArguments()
    {
        $this->assertInstanceOf(ExcludeIf::class, new ExcludeIf(false));
        $this->assertInstanceOf(ExcludeIf::class, new ExcludeIf(true));
        $this->assertInstanceOf(ExcludeIf::class, new ExcludeIf(fn () => true));

        foreach ([1, 1.1, 'phpinfo', new stdClass()] as $condition) {
            try {
                $this->assertInstanceOf(ExcludeIf::class, new ExcludeIf($condition));
                $this->fail('The ExcludeIf constructor must not accept ' . gettype($condition));
            } catch (InvalidArgumentException $exception) {
                $this->assertEquals('The provided condition must be a callable or boolean.', $exception->getMessage());
            }
        }
    }

    public function testItThrowsExceptionIfRuleIsNotSerializable()
    {
        $this->expectException(Exception::class);

        serialize(new ExcludeIf(function () {
            return true;
        }));
    }
}
