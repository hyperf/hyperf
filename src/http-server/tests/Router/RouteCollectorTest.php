<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\HttpServer\Router;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteParser\Std;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\RouteCollector;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RouteCollectorTest extends TestCase
{
    protected function tearDown()
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
        $this->assertSame('Handler::Get', $data['GET']['/']);
        $this->assertSame('Handler::ApiGet', $data['GET']['/api/']);
        $this->assertSame('Handler::Post', $data['POST']['/']);
        $this->assertSame('Handler::ApiPost', $data['POST']['/api/']);
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

        $data = $collector->getData()[0];
        $this->assertSame('Handler::Get', $data['GET']['/']);
        $this->assertSame('Handler::ApiGet', $data['GET']['/api/']);
        $this->assertSame('Handler::Post', $data['POST']['/']);

        $middle = MiddlewareManager::$container;
        $this->assertSame(['GetMiddleware'], $middle['http']['/']['GET']);
        $this->assertSame(['PostMiddleware'], $middle['http']['/']['POST']);
        $this->assertSame(['ApiGetMiddleware', 'ApiSelfGetMiddleware'], $middle['http']['/api/']['GET']);
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
}
