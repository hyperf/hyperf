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

use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Validation\Rules\Enum;
use Hyperf\Validation\Tests\Cases\IntegerStatus;
use Hyperf\Validation\Tests\Cases\PureEnum;
use Hyperf\Validation\Tests\Cases\StringStatus;
use Hyperf\Validation\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

include_once 'fixtures/Enums.php';
/**
 * @internal
 * @coversNothing
 */
class ValidationEnumRuleTest extends TestCase
{
    public function testValidationPassesWhenPassingCorrectEnum(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => 'pending',
                'int_status' => 1,
            ],
            [
                'status' => new Enum(StringStatus::class),
                'int_status' => new Enum(IntegerStatus::class),
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationPassesWhenPassingInstanceOfEnum(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => StringStatus::done,
            ],
            [
                'status' => new Enum(StringStatus::class),
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationPassesWhenPassingInstanceOfPureEnum(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => PureEnum::one,
            ],
            [
                'status' => new Enum(PureEnum::class),
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationFailsWhenProvidingNoExistingCases(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => 'finished',
            ],
            [
                'status' => new Enum(StringStatus::class),
            ]
        );

        $this->assertTrue($v->fails());
        $this->assertEquals(['The selected status is invalid.'], $v->messages()->get('status'));
    }

    public function testValidationPassesForAllCasesUntilEitherOnlyOrExceptIsPassed(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status_1' => PureEnum::one,
                'status_2' => PureEnum::two,
                'status_3' => IntegerStatus::done->value,
            ],
            [
                'status_1' => new Enum(PureEnum::class),
                'status_2' => (new Enum(PureEnum::class))->only([])->except([]),
                'status_3' => new Enum(IntegerStatus::class),
            ],
        );

        $this->assertTrue($v->passes());
    }

    #[DataProvider('conditionalCasesDataProvider')]
    public function testValidationPassesWhenOnlyCasesProvided(
        int|IntegerStatus $enum,
        array|IntegerStatus $only,
        bool $expected
    ): void {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => $enum,
            ],
            [
                'status' => (new Enum(IntegerStatus::class))->only($only),
            ],
        );

        $this->assertSame($expected, $v->passes());
    }

    #[DataProvider('conditionalCasesDataProvider')]
    public function testValidationPassesWhenExceptCasesProvided(
        int|IntegerStatus $enum,
        array|IntegerStatus $except,
        bool $expected
    ): void {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => $enum,
            ],
            [
                'status' => (new Enum(IntegerStatus::class))->except($except),
            ],
        );

        $this->assertSame($expected, $v->fails());
    }

    public function testOnlyHasHigherOrderThanExcept(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => PureEnum::one,
            ],
            [
                'status' => (new Enum(PureEnum::class))
                    ->only(PureEnum::one)
                    ->except(PureEnum::one),
            ],
        );

        $this->assertTrue($v->passes());
    }

    public function testValidationFailsWhenProvidingDifferentType(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => 10,
            ],
            [
                'status' => new Enum(StringStatus::class),
            ]
        );

        $this->assertTrue($v->fails());
        $this->assertEquals(['The selected status is invalid.'], $v->messages()->get('status'));
    }

    public function testValidationPassesWhenProvidingDifferentTypeThatIsCastableToTheEnumType(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => '1',
            ],
            [
                'status' => new Enum(IntegerStatus::class),
            ]
        );

        $this->assertTrue($v->fails());

        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => 1,
            ],
            [
                'status' => new Enum(IntegerStatus::class),
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationFailsWhenProvidingNull(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => null,
            ],
            [
                'status' => new Enum(StringStatus::class),
            ]
        );

        $this->assertTrue($v->fails());
        $this->assertEquals(['The selected status is invalid.'], $v->messages()->get('status'));
    }

    public function testValidationPassesWhenProvidingNullButTheFieldIsNullable(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => null,
            ],
            [
                'status' => ['nullable', new Enum(StringStatus::class)],
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationFailsOnPureEnum(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => 'one',
            ],
            [
                'status' => ['required', new Enum(PureEnum::class)],
            ]
        );

        $this->assertTrue($v->fails());
    }

    public function testValidationFailsWhenProvidingStringToIntegerType(): void
    {
        $v = new Validator(
            $this->getTranslator(),
            [
                'status' => 'abc',
            ],
            [
                'status' => new Enum(IntegerStatus::class),
            ]
        );

        $this->assertTrue($v->fails());
        $this->assertEquals(['The selected status is invalid.'], $v->messages()->get('status'));
    }

    public static function conditionalCasesDataProvider(): array
    {
        return [
            [IntegerStatus::done, IntegerStatus::done, true],
            [IntegerStatus::done, [IntegerStatus::done, IntegerStatus::pending], true],
            [IntegerStatus::pending->value, [IntegerStatus::done, IntegerStatus::pending], true],
            [IntegerStatus::done->value, IntegerStatus::pending, false],
        ];
    }

    private function getTranslator(): Translator
    {
        return new Translator(
            new ArrayLoader(),
            'en'
        );
    }
}
