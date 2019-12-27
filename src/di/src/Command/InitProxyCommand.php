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

namespace Hyperf\Di\Command;

use Hyperf\Command\Command;
use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Container;
use Psr\Container\ContainerInterface;
use Swoole\Timer;
use Symfony\Component\Console\Exception\LogicException;

class InitProxyCommand extends Command
{
    /**
     * Execution in a coroutine environment.
     *
     * @var bool
     */
    protected $coroutine = true;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Scanner
     */
    private $scanner;

    public function __construct(ContainerInterface $container, Scanner $scanner)
    {
        parent::__construct('di:init-proxy');
        $this->container = $container;
        $this->scanner = $scanner;
    }

    public function handle()
    {
        $this->warn('This command does not clear the runtime cache, If you want to delete them, use `vendor/bin/init-proxy.sh` instead.');

        $this->createAopProxies();

        Timer::clearAll();

        $this->output->writeln('<info>Proxy class create success.</info>');
    }

    protected function getScanDir()
    {
        if (! defined('BASE_PATH')) {
            throw new LogicException('BASE_PATH is not defined.');
        }

        $file = BASE_PATH . '/config/autoload/annotations.php';
        if (! file_exists($file)) {
            throw new LogicException(sprintf('Annotations config path[%s] is not exists.', $file));
        }

        $annotations = include $file;
        $scanDirs = $annotations['scan']['paths'] ?? [];
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
            $scanDirs = array_merge($configFromProviders['annotations']['scan']['paths'] ?? [], $scanDirs);
        }

        return $scanDirs;
    }

    private function createAopProxies()
    {
        $scanDirs = $this->getScanDir();

        $meta = $this->scanner->scan($scanDirs);
        $classCollection = array_keys($meta);

        foreach ($classCollection as $item) {
            try {
                $this->container->get($item);
            } catch (\Throwable $ex) {
                // Entry cannot be resolved.
            }
        }

        if ($this->container instanceof Container) {
            foreach ($this->container->getDefinitionSource()->getDefinitions() as $key => $definition) {
                try {
                    $this->container->get($key);
                } catch (\Throwable $ex) {
                    // Entry cannot be resolved.
                }
            }
        }
    }
}
