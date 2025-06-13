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

use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Testing\HttpMessage\Upload\UploadedFile;
use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Validation\Rule;
use Hyperf\Validation\Rules\ImageFile;
use Hyperf\Validation\Validator;
use Hyperf\Validation\ValidatorFactory;
use HyperfTest\Validation\File\FileFactory;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ValidationImageFileRuleTest extends TestCase
{
    protected function setUp(): void
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(ValidatorFactory::class)->andReturn(
            new ValidatorFactory($this->getIlluminateArrayTranslator())
        );
    }

    protected function tearDown(): void
    {
    }

    public function testDimensions()
    {
        $this->fails(
            (new ImageFile())->dimensions(Rule::dimensions()->width(100)->height(100)),
            (new FileFactory())->image('foo.png', 101, 101),
            ['validation.dimensions'],
        );

        $this->passes(
            (new ImageFile())->dimensions(Rule::dimensions()->width(100)->height(100)),
            (new FileFactory())->image('foo.png', 100, 100),
        );
    }

    public function testDimensionsWithCustomImageSizeMethod()
    {
        $this->fails(
            (new ImageFile())->dimensions(Rule::dimensions()->width(100)->height(100)),
            new UploadedFileWithCustomImageSizeMethod(stream_get_meta_data($tmpFile = tmpfile())['uri'], 0, 0, 'foo.png'),
            ['validation.dimensions'],
        );

        $this->passes(
            (new ImageFile())->dimensions(Rule::dimensions()->width(200)->height(200)),
            new UploadedFileWithCustomImageSizeMethod(stream_get_meta_data($tmpFile = tmpfile())['uri'], 0, 0, 'foo.png'),
        );
    }

    public function getIlluminateArrayTranslator(): Translator
    {
        return new Translator(
            new ArrayLoader(),
            'en'
        );
    }

    protected function fails($rule, $values, $messages): void
    {
        $this->assertValidationRules($rule, $values, false, $messages);
    }

    protected function assertValidationRules($rule, $values, $result, $messages): void
    {
        $values = Arr::wrap($values);

        foreach ($values as $value) {
            $v = new Validator(
                $this->getIlluminateArrayTranslator(),
                ['my_file' => $value],
                ['my_file' => is_object($rule) ? clone $rule : $rule]
            );

            $this->assertSame($result, $v->passes());

            $this->assertSame(
                $result ? [] : ['my_file' => $messages],
                $v->messages()->toArray()
            );
        }
    }

    protected function passes($rule, $values): void
    {
        $this->assertValidationRules($rule, $values, true, []);
    }
}

class UploadedFileWithCustomImageSizeMethod extends UploadedFile
{
    public function isValid(): bool
    {
        return true;
    }

    public function guessExtension(): string
    {
        return 'png';
    }

    public function dimensions(): array
    {
        return [200, 200];
    }
}
