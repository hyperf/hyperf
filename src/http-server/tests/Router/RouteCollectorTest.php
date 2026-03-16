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

namespace HyperfTest\HttpServer\Router;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteParser\Std;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\RouteCollector;
use HyperfTest\HttpServer\Stub\RouteCollectorStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RouteCollectorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        MiddlewareManager::$container = [];
    }

    public function testAddRoute()
    {
        $parser = new Std();
        $generator = new DataGenerator();
        $collector = new RouteCollector($parser, $generator);

        $collector->get('/', 'Handler::Get');
        $collector->post('/', 'Handler::Post');
        $collector->addGroup('/api', function ($collector) {
            $collector->get('/', 'Handler::ApiGet');
            $collector->post('/', 'Handler::ApiPost');
        });

        $data = $collector->getData()[0];
        $this->assertSame('Handler::Get', $data['GET']['/']->callback);
        $this->assertSame('Handler::ApiGet', $data['GET']['/api/']->callback);
        $this->assertSame('Handler::Post', $data['POST']['/']->callback);
        $this->assertSame('Handler::ApiPost', $data['POST']['/api/']->callback);
    }

    public function testGetRouteParser()
    {
        $parser = new Std();
        $generator = new DataGenerator();
        $collector = new RouteCollector($parser, $generator);

        $this->assertSame($parser, $collector->getRouteParser());
    }

    public function testAddGroupMiddleware()
    {
        $parser = new Std();
        $generator = new DataGenerator();
        $collector = new RouteCollector($parser, $generator);

        $collector->get('/', 'Handler::Get', [
            'middleware' => ['GetMiddleware'],
        ]);
        $collector->addGroup('/api', function ($collector) {
            $collector->get('/', 'Handler::ApiGet', [
                'middleware' => ['ApiSelfGetMiddleware'],
            ]);
        }, [
            'middleware' => ['ApiGetMiddleware'],
        ]);
        $collector->post('/', 'Handler::Post', [
            'middleware' => ['PostMiddleware'],
        ]);
        $collector->post('/user/{id:\d+}', 'Handler::Post', [
            'middleware' => ['PostMiddleware'],
        ]);

        $data = $collector->getData()[0];
        $this->assertSame('Handler::Get', $data['GET']['/']->callback);
        $this->assertSame('Handler::ApiGet', $data['GET']['/api/']->callback);
        $this->assertSame('Handler::Post', $data['POST']['/']->callback);
        $this->assertSame(['middleware' => ['PostMiddleware']], $data['POST']['/']->options);

        $middle = MiddlewareManager::$container;
        $this->assertSame(['GetMiddleware'], $middle['http']['/']['GET']);
        $this->assertSame(['PostMiddleware'], $middle['http']['/']['POST']);
        $this->assertSame(['ApiGetMiddleware', 'ApiSelfGetMiddleware'], $middle['http']['/api/']['GET']);
        $this->assertSame(['PostMiddleware'], $middle['http']['/user/{id:\d+}']['POST']);
    }

    public function testAddGroupMiddlewareFromAnotherServer()
    {
        $parser = new Std();
        $generator = new DataGenerator();
        $collector = new RouteCollector($parser, $generator, 'test');

        $collector->addGroup('/api', function ($collector) {
            $collector->get('/', 'Handler::ApiGet', [
                'middleware' => ['ApiSelfGetMiddleware'],
            ]);
        }, [
            'middleware' => ['ApiGetMiddleware'],
        ]);

        $middle = MiddlewareManager::$container;
        $this->assertSame(['ApiGetMiddleware', 'ApiSelfGetMiddleware'], $middle['test']['/api/']['GET']);
    }

    public function testRouterCollectorMergeOptions()
    {
        $parser = new Std();
        $generator = new DataGenerator();
        $collector = new RouteCollectorStub($parser, $generator, 'test');

        $origin = [
            'middleware' => ['A', 'B'],
        ];
        $options = [
            'middleware' => ['C', 'B'],
        ];

        $res = $collector->mergeOptions($origin, $options);
        $this->assertSame(['A', 'B', 'C', 'B'], $res['middleware']);
    }

    public function testMiddlewareInOptionalRoute()
    {
        $parser = new Std();
        $generator = new DataGenerator();
        $collector = new RouteCollectorStub($parser, $generator, 'test');

        $routes = [
            '/user/[{id:\d+}]',
            '/role/{id:\d+}',
            '/user',
        ];

        foreach ($routes as $route) {
            $collector->addRoute('GET', $route, 'User::Info', ['middleware' => $middlewares = ['FooMiddleware']]);
            $this->assertSame($middlewares, MiddlewareManager::get('test', $route, 'GET'));
        }
    }
}
