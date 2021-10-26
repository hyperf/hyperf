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

use HyperfTest\Validation\Cases\Stub\ValidatesAttributesStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ValidateAttributesTest extends TestCase
{
    public function testValidateAlpha()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertTrue($validator->validateAlpha('', 'xxx'));
        $this->assertTrue($validator->validateAlpha('', '你好'));

        $this->assertFalse($validator->validateAlpha('', '123'));
        $this->assertFalse($validator->validateAlpha('', '123f1'));
        $this->assertFalse($validator->validateAlpha('', 123));
        $this->assertFalse($validator->validateAlpha('', 123.1));
        $this->assertFalse($validator->validateAlpha('', '123_f1'));
        $this->assertFalse($validator->validateAlpha('', 'xxx_yy'));
    }

    public function testValidateAlphaDash()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertTrue($validator->validateAlphaDash('', 'xxx'));
        $this->assertTrue($validator->validateAlphaDash('', 'xxx_yy'));
        $this->assertTrue($validator->validateAlphaDash('', '你好'));
        $this->assertTrue($validator->validateAlphaDash('', '123'));
        $this->assertTrue($validator->validateAlphaDash('', '123f1'));
        $this->assertTrue($validator->validateAlphaDash('', 123));
        $this->assertTrue($validator->validateAlphaDash('', '123_f1'));

        $this->assertFalse($validator->validateAlphaDash('', 123.1));
    }

    public function testValidateAlphaNum()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertTrue($validator->validateAlphaNum('', 'xxx'));
        $this->assertTrue($validator->validateAlphaNum('', '123'));
        $this->assertTrue($validator->validateAlphaNum('', '123f1'));
        $this->assertTrue($validator->validateAlphaNum('', 123));
        $this->assertTrue($validator->validateAlphaNum('', '你好'));

        $this->assertFalse($validator->validateAlphaNum('', 123.1));
        $this->assertFalse($validator->validateAlphaNum('', '123_f1'));
        $this->assertFalse($validator->validateAlphaNum('', 'xxx_yy'));
    }

    public function testValidateDate()
    {
        $validator = new ValidatesAttributesStub();

        $this->assertFalse($validator->validateDate('', 123));
    }
}
