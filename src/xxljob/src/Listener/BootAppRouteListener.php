<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Listener;

use Exception;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Server\Server;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\XxlJob\Annotation\JobHandler;
use Hyperf\XxlJob\Application;
use Hyperf\XxlJob\Dispatcher\XxlJobRoute;
use Psr\Container\ContainerInterface;

class BootAppRouteListener implements ListenerInterface
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(ContainerInterface $container, Application $app)
    {
        $this->app = $app;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        $container = ApplicationContext::getContainer();
        $logger = $container->get(StdoutLoggerInterface::class);
        $config = $container->get(ConfigInterface::class);
        if (! $config->get('xxl_job.enable', false)) {
            $logger->debug('xxl_job not enable');
            return;
        }
        $prefixUrl = $config->get('xxl_job.prefix_url', 'php-xxl-job');
        $servers = $config->get('server.servers');
        $httpServerRouter = null;
        $serverConfig = null;
        foreach ($servers as $server) {
            $router = $container->get(DispatcherFactory::class)->getRouter($server['name']);
            if (empty($httpServerRouter) && $server['type'] == Server::SERVER_HTTP) {
                $httpServerRouter = $router;
                $serverConfig = $server;
            }
        }
        if (empty($httpServerRouter)) {
            $logger->warning('XxlJob: http Service not started');
            $this->app->getConfig()->setEnable(false);
            return;
        }

        $list = AnnotationCollector::list();
        $this->initAnnotationRoute($list);
        $route = new XxlJobRoute();
        $route->add($httpServerRouter, $prefixUrl);

        $host = $serverConfig['host'];
        if (in_array($host, ['0.0.0.0', 'localhost'])) {
            $host = $this->getIp();
        }

        $xxlJobConfig = $container->get(ConfigInterface::class)->get('xxl_job', []);
        $url = sprintf('http://%s:%s/%s/', $host, $serverConfig['port'], $xxlJobConfig['prefix_url']);
        $this->app->getConfig()->setClientUrl($url);
    }

    private function initAnnotationRoute(array $collector): void
    {
        foreach ($collector as $className => $metadata) {
            if (isset($metadata['_c'][JobHandler::class])) {
                /** @var JobHandler $jobHandler */
                $jobHandler = $metadata['_c'][JobHandler::class];
                Application::setJobHandlers($jobHandler->value, $className);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function getIp(): string
    {
        $ips = swoole_get_local_ip();
        if (is_array($ips) && ! empty($ips)) {
            return current($ips);
        }
        /** @var mixed|string $ip */
        $ip = gethostbyname(gethostname());
        if (is_string($ip)) {
            return $ip;
        }
        throw new Exception('Can not get the internal IP.');
    }
}
