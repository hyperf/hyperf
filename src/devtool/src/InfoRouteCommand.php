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

namespace Hyperf\Devtool;

use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @Command
 */
class InfoRouteCommand extends SymfonyCommand
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

    protected function configure()
    {
        $this->setDescription('Describe the routes information.')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified route information by path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $data = [];

        $factory = $this->container->get(DispatcherFactory::class);
        $router = $factory->getRouter('http');
        [$routers] = $router->getData();
        $path = $input->getOption('path');

        foreach ($routers as $method => $items) {
            foreach ($items as $item) {
                $uri = $item->route;
                if (! is_null($path) && $path != $uri) {
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
                    $serverName = 'http';
                    $registedMiddlewares = MiddlewareManager::get('http', $uri, $method);
                    $middlewares = $this->config->get('middlewares.' . $serverName, []);

                    $middlewares = array_merge($middlewares, $registedMiddlewares);
                    $data[$uri] = [
                        'server' => $serverName,
                        'method' => [$method],
                        'uri' => $uri,
                        'action' => $action,
                        'middleware' => implode(PHP_EOL, array_unique($middlewares)),
                    ];
                }
            }
        }
        $this->show($data, $output);
        $io->success('success.');
    }

    private function show(array $data, OutputInterface $output)
    {
        $rows = [];
        foreach ($data as $route) {
            $route['method'] = implode('|', $route['method']);
            $rows[] = $route;
            $rows[] = new TableSeparator();
        }
        $rows = array_slice($rows, 0, count($rows) - 1);
        $table = new Table($output);
        $table
            ->setHeaders(['Server', 'Method', 'URI', 'Action', 'Middleware'])
            ->setRows($rows);
        $table->render();
    }
}
