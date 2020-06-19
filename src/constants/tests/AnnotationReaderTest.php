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

namespace HyperfTest\Constants;

use Hyperf\Constants\AnnotationReader;
use Hyperf\Constants\ConstantsCollector;
use Hyperf\Constants\UnFormat;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use HyperfTest\Constants\Stub\CustomFormat;
use HyperfTest\Constants\Stub\ErrorCodeStub;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationReaderTest extends TestCase
{
    protected function setUp()
    {
        $reader = new AnnotationReader();

        $ref = new \ReflectionClass(ErrorCodeStub::class);
        $classConstants = $ref->getReflectionConstants();

        $data = $reader->getAnnotations($classConstants);
        ConstantsCollector::set(ErrorCodeStub::class, $data);

        Context::set(sprintf('%s::%s', TranslatorInterface::class, 'locale'), null);
    }

    public function testGetAnnotations()
    {
        $this->getContainer();

        $data = ConstantsCollector::get(ErrorCodeStub::class);

        $this->assertSame('Server Error!', $data[ErrorCodeStub::SERVER_ERROR]['message']);
        $this->assertSame('SHOW ECHO', $data[ErrorCodeStub::SHOW_ECHO]['message']);
        $this->assertSame('ECHO', $data[ErrorCodeStub::SHOW_ECHO]['echo']);

        $this->assertArrayNotHasKey(ErrorCodeStub::NO_MESSAGE, $data);
    }

    public function testGetMessageWithArguments()
    {
        $this->getContainer();

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::PARAMS_INVALID);

        $this->assertSame('Params[%s] is invalid.', $res);

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::PARAMS_INVALID, 'user_id');

        $this->assertSame('Params[user_id] is invalid.', $res);

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::PARAMS_INVALID, ['order_id']);

        $this->assertSame('Params[order_id] is invalid.', $res);
    }

    public function testGetMessageUsingTranslator()
    {
        $container = $this->getContainer(true);

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::SERVER_ERROR);
        $this->assertSame('Server Error!', $res);

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::TRANSLATOR_ERROR_MESSAGE);
        $this->assertSame('Error Message', $res);

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::TRANSLATOR_NOT_EXIST, ['name' => 'Hyperf']);
        $this->assertSame('Hyperf is not exist.', $res);

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::PARAMS_INVALID, 'user_id');
        $this->assertSame('Params[user_id] is invalid.', $res);

        $res = ErrorCodeStub::getMessage(ErrorCodeStub::PARAMS_INVALID, ['order_id']);
        $this->assertSame('Params[order_id] is invalid.', $res);

        Context::set(sprintf('%s::%s', TranslatorInterface::class, 'locale'), 'zh_CN');
        $res = ErrorCodeStub::getMessage(ErrorCodeStub::TRANSLATOR_ERROR_MESSAGE);
        $this->assertSame('错误信息', $res);
    }

    public function testGetMessageToArray()
    {
        $expect = [
            500 => 'Server Error!',
            501 => 'SHOW ECHO',
            503 => 'Params[%s] is invalid.',
            504 => 'error.message',
            505 => 'error.not_exist',
        ];

        $data = ErrorCodeStub::getMessageToArray();

        $unFormatData = ErrorCodeStub::getMessageToArray(new UnFormat());

        $collectorData = ConstantsCollector::getMessageToArray(ErrorCodeStub::class);

        $this->assertSame($expect, $data);
        $this->assertSame($expect, $unFormatData);
        $this->assertSame($expect, $collectorData);
    }

    public function testGetMessageToArrayUseFormat()
    {
        $expect = [
            ['code' => 500, 'msg' => 'Server Error!'],
            ['code' => 501, 'msg' => 'SHOW ECHO'],
            ['code' => 503, 'msg' => 'Params[%s] is invalid.'],
            ['code' => 504, 'msg' => 'error.message'],
            ['code' => 505, 'msg' => 'error.not_exist'],
        ];

        $data = ErrorCodeStub::getMessageToArray(new CustomFormat());

        $this->assertSame($expect, $data);
    }

    public function testSameValueDifferentKey()
    {
        $container = $this->getContainer(true);

        $this->assertSame('Status enabled', ErrorCodeStub::getStatus(ErrorCodeStub::STATUS_ENABLE));
        $this->assertSame('Status disabled', ErrorCodeStub::getStatus(ErrorCodeStub::STATUS_DISABLE));

        $this->assertSame('Type enabled', ErrorCodeStub::getType(ErrorCodeStub::TYPE_ENABLE));
    }

    protected function getContainer($has = false)
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(TranslatorInterface::class)->andReturnUsing(function () {
            $loader = new ArrayLoader();
            $loader->addMessages('en', 'error', [
                'message' => 'Error Message',
                'not_exist' => ':name is not exist.',
            ]);
            $loader->addMessages('zh_CN', 'error', ['message' => '错误信息']);
            return new Translator($loader, 'en');
        });

        $container->shouldReceive('has')->andReturn($has);

        return $container;
    }
}
