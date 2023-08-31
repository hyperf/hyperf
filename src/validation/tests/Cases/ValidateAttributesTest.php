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
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ValidateAttributesTest extends TestCase
{
    public function testValidateAlpha()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertTrue($validator->validateAlpha('', 'xxx', []));
        $this->assertTrue($validator->validateAlpha('', '你好', []));

        $this->assertFalse($validator->validateAlpha('', '123', []));
        $this->assertFalse($validator->validateAlpha('', '123f1', []));
        $this->assertFalse($validator->validateAlpha('', 123, []));
        $this->assertFalse($validator->validateAlpha('', 123.1, []));
        $this->assertFalse($validator->validateAlpha('', '123_f1', []));
        $this->assertFalse($validator->validateAlpha('', 'xxx_yy', []));
    }

    public function testValidateAlphaDash()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertTrue($validator->validateAlphaDash('', 'xxx', []));
        $this->assertTrue($validator->validateAlphaDash('', 'xxx_yy', []));
        $this->assertTrue($validator->validateAlphaDash('', '你好', []));
        $this->assertTrue($validator->validateAlphaDash('', '123', []));
        $this->assertTrue($validator->validateAlphaDash('', '123f1', []));
        $this->assertTrue($validator->validateAlphaDash('', 123, []));
        $this->assertTrue($validator->validateAlphaDash('', '123_f1', []));
        $this->assertTrue($validator->validateAlphaDash('', 'नमस्कार-_', []));

        $this->assertFalse($validator->validateAlphaDash('', 123.1, []));
        $this->assertFalse($validator->validateAlphaDash('', 'abc\n', []));
        $this->assertFalse($validator->validateAlphaDash('', 'http://-g232oogle.com', []));
    }

    public function testValidateAlphaNum()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertTrue($validator->validateAlphaNum('', 'xxx', []));
        $this->assertTrue($validator->validateAlphaNum('', '123', []));
        $this->assertTrue($validator->validateAlphaNum('', '123f1', []));
        $this->assertTrue($validator->validateAlphaNum('', 123, []));
        $this->assertTrue($validator->validateAlphaNum('', '你好', []));
        $this->assertTrue($validator->validateAlphaNum('', '57', []));

        $this->assertFalse($validator->validateAlphaNum('', 'नमस्कार-_', []));
        $this->assertFalse($validator->validateAlphaNum('', 123.1, []));
        $this->assertFalse($validator->validateAlphaNum('', '123_f1', []));
        $this->assertFalse($validator->validateAlphaNum('', 'xxx_yy', []));
        $this->assertFalse($validator->validateAlphaNum('', 'abc\n', []));
        $this->assertFalse($validator->validateAlphaNum('', 'ユニコードを基盤技術と-_123', []));
        $this->assertFalse($validator->validateAlphaNum('', 'http://-g232oogle.com', []));
    }

    public function testValidateDate()
    {
        $validator = new ValidatesAttributesStub();

        $this->assertFalse($validator->validateDate('', 123));
    }

    public function testValidateAscii()
    {
        $validator = new ValidatesAttributesStub();

        $this->assertTrue($validator->validateAscii('', '\x0A'));
        $this->assertTrue($validator->validateAscii('', '123'));
        $this->assertTrue($validator->validateAscii('', 123.1));
        $this->assertTrue($validator->validateAscii('', '123f1'));
        $this->assertTrue($validator->validateAscii('', 'xxx_yy'));

        $this->assertFalse($validator->validateAscii('', '你好'));
        $this->assertFalse($validator->validateAscii('', 'ユニコードを基盤技術と-_123'));
        $this->assertFalse($validator->validateAscii('', 'नमस्कार-_'));
    }
}
