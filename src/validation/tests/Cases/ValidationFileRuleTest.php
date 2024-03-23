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
use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Validation\Rules\File;
use Hyperf\Validation\Validator;
use Hyperf\Validation\ValidatorFactory;
use HyperfTest\Validation\File\FileFactory;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ValidationFileRuleTest extends TestCase
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

    public function testBasic()
    {
        $this->fails(
            File::default(),
            'foo',
            ['validation.file'],
        );

        $this->passes(
            File::default(),
            (new FileFactory())->create('foo.bar'),
        );

        $this->passes(File::default(), null);
    }

    public function testSingleMimetype()
    {
        $this->fails(
            File::types('text/plain'),
            (new FileFactory())->createWithContent('foo.png', file_get_contents(__DIR__ . '/fixtures/image.png')),
            ['validation.mimetypes']
        );

        $this->passes(
            File::types('image/png'),
            (new FileFactory())->createWithContent('foo.png', file_get_contents(__DIR__ . '/fixtures/image.png')),
        );
    }

    public function testMultipleMimeTypes()
    {
        $this->fails(
            File::types(['text/plain', 'image/jpeg']),
            (new FileFactory())->createWithContent('foo.png', file_get_contents(__DIR__ . '/fixtures/image.png')),
            ['validation.mimetypes']
        );

        $this->passes(
            File::types(['text/plain', 'image/png']),
            (new FileFactory())->createWithContent('foo.png', file_get_contents(__DIR__ . '/fixtures/image.png')),
        );
    }

    public function testSingleMime()
    {
        $this->fails(
            File::types('txt'),
            (new FileFactory())->createWithContent('foo.png', file_get_contents(__DIR__ . '/fixtures/image.png')),
            ['validation.mimes']
        );

        $this->passes(
            File::types('png'),
            (new FileFactory())->createWithContent('foo.png', file_get_contents(__DIR__ . '/fixtures/image.png')),
        );
    }

    public function testMultipleMimes()
    {
        $this->fails(
            File::types(['png', 'jpg', 'jpeg', 'svg']),
            (new FileFactory())->createWithContent('foo.txt', 'Hello World!'),
            ['validation.mimes']
        );

        $this->passes(
            File::types(['png', 'jpg', 'jpeg', 'svg']),
            [
                (new FileFactory())->createWithContent('foo.png', file_get_contents(__DIR__ . '/fixtures/image.png')),
                (new FileFactory())->createWithContent('foo.svg', file_get_contents(__DIR__ . '/fixtures/image.svg')),
            ]
        );
    }

    public function testMixOfMimetypesAndMimes()
    {
        $this->fails(
            File::types(['png', 'image/png']),
            (new FileFactory())->createWithContent('foo.txt', 'Hello World!'),
            ['validation.mimetypes', 'validation.mimes']
        );

        $this->passes(
            File::types(['png', 'image/png']),
            (new FileFactory())->createWithContent('foo.png', file_get_contents(__DIR__ . '/fixtures/image.png')),
        );
    }

    public function testImage()
    {
        $this->fails(
            File::image(),
            (new FileFactory())->createWithContent('foo.txt', 'Hello World!'),
            ['validation.image']
        );

        $this->passes(
            File::image(),
            (new FileFactory())->image('foo.png'),
        );
    }

    public function testSize()
    {
        $this->fails(
            File::default()->size(1024),
            [
                (new FileFactory())->create('foo.txt', 1025),
                (new FileFactory())->create('foo.txt', 1023),
            ],
            ['validation.size.file']
        );

        $this->passes(
            File::default()->size(1024),
            (new FileFactory())->create('foo.txt', 1024),
        );
    }

    public function testBetween()
    {
        $this->fails(
            File::default()->between(1024, 2048),
            [
                (new FileFactory())->create('foo.txt', 1023),
                (new FileFactory())->create('foo.txt', 2049),
            ],
            ['validation.between.file']
        );

        $this->passes(
            File::default()->between(1024, 2048),
            [
                (new FileFactory())->create('foo.txt', 1024),
                (new FileFactory())->create('foo.txt', 2048),
                (new FileFactory())->create('foo.txt', 1025),
                (new FileFactory())->create('foo.txt', 2047),
            ]
        );
    }

    public function testMin()
    {
        $this->fails(
            File::default()->min(1024),
            (new FileFactory())->create('foo.txt', 1023),
            ['validation.min.file']
        );

        $this->passes(
            File::default()->min(1024),
            [
                (new FileFactory())->create('foo.txt', 1024),
                (new FileFactory())->create('foo.txt', 1025),
                (new FileFactory())->create('foo.txt', 2048),
            ]
        );
    }

    public function testMinWithHumanReadableSize()
    {
        $this->fails(
            File::default()->min('1024kb'),
            (new FileFactory())->create('foo.txt', 1023),
            ['validation.min.file']
        );

        $this->passes(
            File::default()->min('1024kb'),
            [
                (new FileFactory())->create('foo.txt', 1024),
                (new FileFactory())->create('foo.txt', 1025),
                (new FileFactory())->create('foo.txt', 2048),
            ]
        );
    }

    public function testMax()
    {
        $this->fails(
            File::default()->max(1024),
            (new FileFactory())->create('foo.txt', 1025),
            ['validation.max.file']
        );

        $this->passes(
            File::default()->max(1024),
            [
                (new FileFactory())->create('foo.txt', 1024),
                (new FileFactory())->create('foo.txt', 1023),
                (new FileFactory())->create('foo.txt', 512),
            ]
        );
    }

    public function testMaxWithHumanReadableSize()
    {
        $this->fails(
            File::default()->max('1024kb'),
            (new FileFactory())->create('foo.txt', 1025),
            ['validation.max.file']
        );

        $this->passes(
            File::default()->max('1024kb'),
            [
                (new FileFactory())->create('foo.txt', 1024),
                (new FileFactory())->create('foo.txt', 1023),
                (new FileFactory())->create('foo.txt', 512),
            ]
        );
    }

    public function testMaxWithHumanReadableSizeAndMultipleValue()
    {
        $this->fails(
            File::default()->max('1mb'),
            (new FileFactory())->create('foo.txt', 1025),
            ['validation.max.file']
        );

        $this->passes(
            File::default()->max('1mb'),
            [
                (new FileFactory())->create('foo.txt', 1000),
                (new FileFactory())->create('foo.txt', 999),
                (new FileFactory())->create('foo.txt', 512),
            ]
        );
    }

    public function testMacro()
    {
        File::macro('toDocument', function () {
            return static::default()->rules('mimes:txt,csv');
        });

        $this->fails(
            File::toDocument(),
            (new FileFactory())->create('foo.png'),
            ['validation.mimes']
        );

        $this->passes(
            File::toDocument(),
            [
                (new FileFactory())->create('foo.txt'),
                (new FileFactory())->create('foo.csv'),
            ]
        );
    }

    public function testItCanSetDefaultUsing()
    {
        $this->assertInstanceOf(File::class, File::default());

        File::defaults(function () {
            return File::types('txt')->max(12 * 1024);
        });

        $this->fails(
            File::default(),
            (new FileFactory())->create('foo.png', 13 * 1024),
            [
                'validation.mimes',
                'validation.max.file',
            ]
        );

        File::defaults(File::image()->between(1024, 2048));

        $this->passes(
            File::default(),
            (new FileFactory())->create('foo.png', (int) (1.5 * 1024)),
        );
    }

    public function getIlluminateArrayTranslator(): Translator
    {
        return new Translator(
            new ArrayLoader(),
            'en'
        );
    }

    protected function fails($rule, $values, $messages)
    {
        $this->assertValidationRules($rule, $values, false, $messages);
    }

    protected function assertValidationRules($rule, $values, $result, $messages)
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

    protected function passes($rule, $values)
    {
        $this->assertValidationRules($rule, $values, true, []);
    }
}
