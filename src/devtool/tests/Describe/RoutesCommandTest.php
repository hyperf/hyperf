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

namespace HyperfTest\Devtool\Describe;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Devtool\Describe\RoutesCommand;
use Hyperf\HttpServer\Router\Handler;
use HyperfTest\Devtool\Stub\ContainerStub;
use HyperfTest\Devtool\Stub\IndexController;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class RoutesCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testAnalyzeHandler()
    {
        $container = ContainerStub::getContainer();
        $command = new RoutesCommand($container, $container->get(ConfigInterface::class));

        $ref = new ReflectionClass($command);
        $method = $ref->getMethod('analyzeHandler');

        $data = [];
        $method->invokeArgs($command, [&$data, 'http', 'GET', null, new Handler(IndexController::class . '::index', '/')]);
        $method->invokeArgs($command, [&$data, 'http', 'GET', null, new Handler([IndexController::class, 'index2'], '/index2')]);
        $method->invokeArgs($command, [&$data, 'http', 'GET', null, new Handler(IndexController::class . '@index3', '/index3')]);
        $method->invokeArgs($command, [&$data, 'http', 'GET', null, new Handler(function () {
            return '';
        }, '/index4')]);
        $method->invokeArgs($command, [&$data, 'http', 'POST', null, new Handler(IndexController::class . '::index5', '/index5')]);

        $this->assertSame(5, count($data));
    }
}
