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

namespace Hyperf\Devtool\Describe;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class RoutesCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        parent::__construct('describe:routes');
        $this->container = $container;
        $this->config = $config;
    }

    public function handle()
    {
        $path = $this->input->getOption('path');
        $server = $this->input->getOption('server');

        $data = [];

        $factory = $this->container->get(DispatcherFactory::class);
        $router = $factory->getRouter($server);
        [$routers] = $router->getData();

        foreach ($routers as $method => $items) {
            foreach ($items as $item) {
                $uri = $item->route;
                if (! is_null($path) && ! Str::contains($uri, $path)) {
                    continue;
                }
                if (is_array($item->callback)) {
                    $action = $item->callback[0] . '::' . $item->callback[1];
                } else {
                    $action = $item->callback;
                }
                if (isset($data[$uri])) {
                    $data[$uri]['method'][] = $method;
                } else {
                    // method,uri,name,action,middleware
                    $registedMiddlewares = MiddlewareManager::get($server, $uri, $method);
                    $middlewares = $this->config->get('middlewares.' . $server, []);

                    $middlewares = array_merge($middlewares, $registedMiddlewares);
                    $data[$uri] = [
                        'server' => $server,
                        'method' => [$method],
                        'uri' => $uri,
                        'action' => $action,
                        'middleware' => implode(PHP_EOL, array_unique($middlewares)),
                    ];
                }
            }
        }
        $this->show($data);
        $this->output->success('success.');
    }

    protected function configure()
    {
        $this->setDescription('Describe the routes information.')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified route information by path')
            ->addOption('server', 'S', InputOption::VALUE_OPTIONAL, 'Which server you want to describe routes.', 'http');
    }

    private function show(array $data)
    {
        $rows = [];
        foreach ($data as $route) {
            $route['method'] = implode('|', $route['method']);
            $rows[] = $route;
            $rows[] = new TableSeparator();
        }
        $rows = array_slice($rows, 0, count($rows) - 1);
        $table = new Table($this->output);
        $table
            ->setHeaders(['Server', 'Method', 'URI', 'Action', 'Middleware'])
            ->setRows($rows);
        $table->render();
    }
}
