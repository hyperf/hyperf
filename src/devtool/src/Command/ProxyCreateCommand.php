<?php

namespace Hyperf\Devtool\Command;

use Hyperf\Config\ProviderConfig;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Definition\DefinitionSourceInterface;
use Hyperf\Framework\Server;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProxyCreateCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var Scanner
     */
    private $scanner;

    public function __construct(ContainerInterface $container, Scanner $scanner)
    {
        parent::__construct('proxy:create');
        $this->container = $container;
        $this->scanner = $scanner;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = BASE_PATH . '/config/autoload/annotations.php';
        if (!file_exists($file)) {
            $output->writeln("<error>ERROR</error> Annotations config path[$file] is not exists.");
            exit(0);
        }

        $annotations = include $file;
        $configFromProviders = ProviderConfig::load();
        $scanDirs = $configFromProviders['scan']['paths'];
        $scanDirs = array_merge($scanDirs, $annotations['scan']['paths'] ?? []);

        $classCollection = $this->scanner->scan($scanDirs);

        foreach ($classCollection as $item) {
            try {
                $this->container->get($item);
            } catch (\Throwable $ex) {
                // Entry cannot be resoleved.
            }
        }

        $output->writeln("<info>Proxy class create success.</info>");
    }
}