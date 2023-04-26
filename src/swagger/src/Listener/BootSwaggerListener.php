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
namespace Hyperf\Swagger\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Engine\Constant\SocketType;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Hyperf\Swagger\Annotation as SA;
use Hyperf\Swagger\Generator;
use Hyperf\Swagger\HttpServer;
use Hyperf\Swagger\Util;
use InvalidArgumentException;
use OpenApi\Annotations\Operation;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\value;

class BootSwaggerListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $config = $this->container->get(ConfigInterface::class);
        if (! $config->get('swagger.enable', false)) {
            return;
        }

        $port = $config->get('swagger.port', 9500);

        // Setup SwaggerUI Server
        $servers = $config->get('server.servers');
        foreach ($servers as $server) {
            if ($server['port'] == $port) {
                throw new InvalidArgumentException(sprintf('The swagger server port is invalid. Because it is conflicted with %s server.', $server['name']));
            }
        }

        $servers[] = [
            'name' => uniqid(),
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => $port,
            'sock_type' => SocketType::TCP,
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, 'onRequest'],
            ],
        ];

        $config->set('server.servers', $servers);

        if ($config->get('swagger.auto_generate', false)) {
            $this->container->get(Generator::class)->generate();
        }

        // Init Router
        $factory = $this->container->get(DispatcherFactory::class);
        $annotations = [
            SA\Get::class,
            SA\Head::class,
            SA\Patch::class,
            SA\Post::class,
            SA\Put::class,
            SA\Delete::class,
            SA\Options::class,
        ];

        foreach ($annotations as $annotation) {
            $methodCollector = AnnotationCollector::getMethodsByAnnotation($annotation);
            foreach ($methodCollector as $item) {
                $class = $item['class'];
                $method = $item['method'];
                /** @var MultipleAnnotation $annotation */
                $annotation = $item['annotation'];

                $classAnnotations = AnnotationCollector::getClassAnnotations($class);
                $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($class, $method);

                $serverAnnotations = Util::findAnnotations($methodAnnotations, SA\HyperfServer::class);
                if (! $serverAnnotations) {
                    $serverAnnotations = Util::findAnnotations($classAnnotations, SA\HyperfServer::class);
                }

                $middlewareAnnotations = Util::findAnnotations($methodAnnotations, Middleware::class);
                $middlewareAnnotations = array_merge($middlewareAnnotations, Util::findAnnotations($classAnnotations, Middleware::class));

                /** @var Operation $opera */
                foreach ($annotation->toAnnotations() as $opera) {
                    /** @var SA\HyperfServer $serverAnnotation */
                    foreach ($serverAnnotations as $serverAnnotation) {
                        $factory->getRouter($serverAnnotation->name)->addRoute(
                            [$opera->method],
                            $opera->path,
                            [$class, $method],
                            [
                                'middleware' => value(static function () use ($middlewareAnnotations) {
                                    $result = [];
                                    /** @var Middleware $annotation */
                                    foreach ($middlewareAnnotations as $annotation) {
                                        $result[] = $annotation->middleware;
                                    }

                                    return $result;
                                }),
                            ]
                        );
                    }
                }
            }
        }
    }
}
