<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
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
    public function testValidateAlphaNum()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertTrue($validator->validateAlphaNum('', 'xxx'));
        $this->assertTrue($validator->validateAlphaNum('', '123'));
        $this->assertTrue($validator->validateAlphaNum('', '123f1'));
        $this->assertTrue($validator->validateAlphaNum('', 123));

        $this->assertFalse($validator->validateAlphaNum('', 123.1));
        $this->assertFalse($validator->validateAlphaNum('', '123_f1'));
    }

    public function testValidateAfter()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertTrue($validator->validateAfter('', '2020-05-20', ['2020-05-19']));
        $this->assertFalse($validator->validateAfter('', '2020-05-18', ['2020-05-19']));
        $this->assertFalse($validator->validateAfter('', '', ['2020-05-19']));
        $this->assertFalse($validator->validateAfter('', null, ['2020-05-19']));
    }

    public function testValidateBefore()
    {
        $validator = new ValidatesAttributesStub();
        $this->assertFalse($validator->validateBefore('', '2020-05-20', ['2020-05-19']));
        $this->assertTrue($validator->validateBefore('', '2020-05-18', ['2020-05-19']));
        $this->assertFalse($validator->validateBefore('', '', ['2020-05-19']));
        $this->assertFalse($validator->validateBefore('', null, ['2020-05-19']));
    }
}
