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

namespace Hyperf\Di\Command;

use Hyperf\Command\Command;
use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Container;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class InitProxyCommand extends Command
{
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
        $scanDirs = $this->getScanDir();

        $runtime = BASE_PATH . '/runtime/container/proxy/';
        if (is_dir($runtime)) {
            $this->clearRuntime($runtime);
        }

        $classCollection = $this->scanner->scan($scanDirs);

        foreach ($classCollection as $item) {
            try {
                $this->container->get($item);
            } catch (\Throwable $ex) {
                // Entry cannot be resoleved.
            }
        }

        if ($this->container instanceof Container) {
            foreach ($this->container->getDefinitionSource()->getDefinitions() as $key => $definition) {
                try {
                    $this->container->get($key);
                } catch (\Throwable $ex) {
                    // Entry cannot be resoleved.
                }
            }
        }

        $this->output->writeln('<info>Proxy class create success.</info>');
    }

    protected function clearRuntime($paths)
    {
        $finder = new Finder();
        $finder->files()->in($paths)->name('*.php');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $path = $file->getRealPath();
            @unlink($path);
        }
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
            $scanDirs = array_merge($configFromProviders['scan']['paths'] ?? [], $scanDirs);
        }

        return $scanDirs;
    }
}
