<?php
namespace Hyperf\Apidog\Middleware;

use FastRoute\Dispatcher;
use Hyperf\Apidog\Annotation\Body;
use Hyperf\Apidog\Annotation\FormData;
use Hyperf\Apidog\Annotation\Header;
use Hyperf\Apidog\Annotation\Query;
use Hyperf\Apidog\ApiAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Apidog\Validation;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Context;

class ValidationMiddleware extends CoreMiddleware
{

    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var HttpResponse
     */
    protected $response;
    /**
     * @var LoggerFactory
     */
    protected $log;
    /**
     * @Inject()
     * @var \Hyperf\Apidog\Validation\ValidationInterface
     */
    protected $validation;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request, LoggerFactory $logger)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
        $this->log = $logger->get('validation');
        parent::__construct($container, 'http');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $routes = $this->dispatcher->dispatch($request->getMethod(), $uri->getPath());
        if ($routes[0] !== Dispatcher::FOUND) {

            return $handler->handle($request);
        }
        [$controller, $action] = $this->prepareHandler($routes[1]);
        $controllerInstance = $this->container->get($controller);
        $annotations = ApiAnnotation::methodMetadata($controller, $action);
        $header_rules = [];
        $query_rules = [];
        $body_rules = [];
        $form_data_rules = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Header) {
                $header_rules[$annotation->key] = $annotation->rule;
            }
            if ($annotation instanceof Query) {
                $query_rules[$annotation->key] = $annotation->rule;
            }
            if ($annotation instanceof Body) {
                $body_rules = $annotation->rules;
            }
            if ($annotation instanceof FormData) {
                $form_data_rules[$annotation->key] = $annotation->rule;
            }
        }

        if ($header_rules) {
            $headers = $request->getHeaders();
            $headers = array_map(function($item) {
                return $item[0];
            }, $headers);
            [$data, $error] = $this->check($header_rules, $headers, $controllerInstance);
            if ($data === false) {
                return $this->response->json([
                    'code' => -1,
                    'message' => $error
                ]);
            }
        }

        if ($query_rules) {
            [$data, $error] = $this->check($query_rules, $request->getQueryParams(), $controllerInstance);
            if ($data === false) {
                return $this->response->json([
                    'code' => -1,
                    'message' => $error
                ]);
            }
            Context::set(ServerRequestInterface::class, $request->withQueryParams($data));
        }

        if ($body_rules) {
            [$data, $error] = $this->check($body_rules, (array)json_decode($request->getBody()->getContents(), true), $controllerInstance);
            if ($data === false) {
                return $this->response->json([
                    'code' => -1,
                    'message' => $error
                ]);
            }
            Context::set(ServerRequestInterface::class, $request->withBody(new SwooleStream(json_encode($data))));
        }

        if ($form_data_rules) {
            [$data, $error] = $this->check($form_data_rules, $request->getParsedBody(), $controllerInstance);
            if ($data === false) {
                return $this->response->json([
                    'code' => -1,
                    'message' => $error
                ]);
            }
            Context::set(ServerRequestInterface::class, $request->withParsedBody($data));
        }

        return $handler->handle($request);
    }

    public function check($rules, $data, $controllerInstance)
    {
        $validated_data = $this->validation->check($rules, $data, $controllerInstance);

        return [$validated_data, $this->validation->getError()];
    }
}