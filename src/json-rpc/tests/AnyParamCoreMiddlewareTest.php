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

namespace HyperfTest\JsonRpc;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Event\EventDispatcherFactory;
use Hyperf\Event\ListenerProviderFactory;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\JsonRpc\CoreMiddleware;
use Hyperf\JsonRpc\DataFormatter;
use Hyperf\JsonRpc\JsonRpcTransporter;
use Hyperf\JsonRpc\NormalizeDataFormatter;
use Hyperf\Logger\Logger;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\PathGenerator;
use Hyperf\RpcServer\Router\DispatcherFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Packer\JsonPacker;
use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\SymfonyNormalizer;
use HyperfTest\JsonRpc\Stub\CalculatorService;
use kuiper\docReader\DocReader;
use kuiper\docReader\DocReaderInterface;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 * @coversNothing
 */
class AnyParamCoreMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $container = $this->createContainer();
        $router = $container->get(DispatcherFactory::class)->getRouter('jsonrpc');
        $router->addRoute('/CalculatorService/sum', [
            CalculatorService::class, 'sum',
        ]);
        $middleware = new CoreMiddleware($container, 'jsonrpc');
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $request = (new Request('POST', new Uri('/CalculatorService/sum')))
            ->withParsedBody([
                ['value' => 1],
                ['value' => 2],
            ]);
        Context::set(ResponseInterface::class, new Response());

        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
        $ret = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('result', $ret);
        $this->assertEquals(['value' => 3], $ret['result']);
    }

    public function testException()
    {
        $container = $this->createContainer();
        $router = $container->get(DispatcherFactory::class)->getRouter('jsonrpc');
        $router->addRoute('/CalculatorService/divide', [
            CalculatorService::class, 'divide',
        ]);
        $middleware = new CoreMiddleware($container, 'jsonrpc');
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $request = (new Request('POST', new Uri('/CalculatorService/divide')))
            ->withParsedBody([3, 0]);
        Context::set(ResponseInterface::class, new Response());

        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
        $ret = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('error', $ret);
        $this->assertArrayHasKey('data', $ret['error']);

        $this->assertEquals(\InvalidArgumentException::class, $ret['error']['data']['class']);
        $this->assertArraySubset([
            'message' => 'Expected non-zero value of divider',
            'code' => 0,
            'file' => '/mnt/home/ywb/src/php/hyperf/src/json-rpc/tests/Stub/CalculatorService.php',
        ], $ret['error']['data']['attributes']);
    }

    public function createContainer()
    {
        $container = new Container(new DefinitionSource([
            NormalizerInterface::class => SymfonyNormalizer::class,
            Serializer::class => SerializerFactory::class,
            DocReaderInterface::class => DocReader::class,
            DataFormatter::class => NormalizeDataFormatter::class,
            MethodDefinitionCollectorInterface::class => MethodDefinitionCollector::class,
            StdoutLoggerInterface::class => function () {
                return new Logger('App', [new StreamHandler('php://stderr')]);
            },
            ConfigInterface::class => function () {
                return new Config([
                    'protocols' => [
                        'jsonrpc' => [
                            'packer' => JsonPacker::class,
                            'transporter' => JsonRpcTransporter::class,
                            'path-generator' => PathGenerator::class,
                            'data-formatter' => DataFormatter::class,
                        ],
                    ],
                ]);
            },
            ListenerProviderInterface::class => ListenerProviderFactory::class,
            EventDispatcherInterface::class => EventDispatcherFactory::class,
            PathGeneratorInterface::class => PathGenerator::class,
        ], [], new Scanner()));
        ApplicationContext::setContainer($container);
        return $container;
    }
}
