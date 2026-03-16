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
use Hyperf\Validation\Rules\ProhibitedIf;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class ValidationProhibitedIfTest extends TestCase
{
    public function testItReturnsStringVersionOfRuleWhenCast()
    {
        $rule = new ProhibitedIf(function () {
            return true;
        });

        $this->assertSame('prohibited', (string) $rule);

        $rule = new ProhibitedIf(function () {
            return false;
        });

        $this->assertSame('', (string) $rule);

        $rule = new ProhibitedIf(true);

        $this->assertSame('prohibited', (string) $rule);

        $rule = new ProhibitedIf(false);

        $this->assertSame('', (string) $rule);
    }

    public function testItValidatesCallableAndBooleanAreAcceptableArguments()
    {
        $this->assertInstanceOf(ProhibitedIf::class, new ProhibitedIf(false));
        $this->assertInstanceOf(ProhibitedIf::class, new ProhibitedIf(true));
        $this->assertInstanceOf(ProhibitedIf::class, new ProhibitedIf(fn () => true));

        foreach ([1, 1.1, 'phpinfo', new stdClass()] as $condition) {
            try {
                $this->assertInstanceOf(ProhibitedIf::class, new ProhibitedIf($condition));
                $this->fail('The ProhibitedIf constructor must not accept ' . gettype($condition));
            } catch (InvalidArgumentException $exception) {
                $this->assertEquals('The provided condition must be a callable or boolean.', $exception->getMessage());
            }
        }
    }

    public function testItThrowsExceptionIfRuleIsNotSerializable()
    {
        $this->expectException(Exception::class);

        serialize(new ProhibitedIf(function () {
            return true;
        }));
    }
}
