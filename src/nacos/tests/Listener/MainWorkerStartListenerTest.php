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
namespace HyperfTest\Nacos\Listener;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Nacos\Listener\MainWorkerStartListener;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use HyperfTest\Nacos\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 * @coversNothing
 */
class MainWorkerStartListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testUpdateServiceAndInstance()
    {
        $handler = function (RequestInterface $request) {
            $method = $request->getMethod();
            $data = '{}';
            if ($method === 'PUT') {
                $data = 'ok';
            }

            return new FulfilledPromise(new Psr7\Response(
                200,
                [],
                $data
            ));
        };
        $container = ContainerStub::getContainer($handler);

        $listener = new MainWorkerStartListener($container);

        $listener->process(new MainWorkerStart(new \stdClass(), 1));

        $this->assertTrue(true);
    }

    public function testRegisterServiceAndInstance()
    {
        $handler = function (RequestInterface $request) {
            $method = $request->getMethod();
            $status = 404;
            $data = '{}';
            if ($method === 'POST') {
                $data = 'ok';
                $status = 200;
            }
            if ($request->getUri()->getPath() === '/nacos/v1/cs/configs') {
                $data = '{}';
                $status = 200;
            }

            return new FulfilledPromise(new Psr7\Response(
                $status,
                [],
                $data
            ));
        };
        $container = ContainerStub::getContainer($handler);

        $listener = new MainWorkerStartListener($container);

        $listener->process(new MainWorkerStart(new \stdClass(), 1));

        $this->assertTrue(true);
    }

    public function testRefreshConfigForMainCoroutineServerStart()
    {
        $handler = function (RequestInterface $request) {
            $method = $request->getMethod();
            $data = '{}';
            if ($method === 'PUT') {
                $data = 'ok';
            }
            if ($request->getUri()->getPath() === '/nacos/v1/cs/configs') {
                $data = Json::encode(['id' => uniqid()]);
            }

            return new FulfilledPromise(new Psr7\Response(
                200,
                [],
                $data
            ));
        };
        $container = ContainerStub::getContainer($handler);

        $listener = new MainWorkerStartListener($container);

        $listener->process(new MainCoroutineServerStart('test', new \stdClass(), []));

        $config = $container->get(ConfigInterface::class);
        $id = $config->get('nacos_default_config.id');
        sleep(5);
        $this->assertNotEquals($id, $config->get('nacos_default_config.id'));

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
    }
}
