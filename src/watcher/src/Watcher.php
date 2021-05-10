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
namespace Hyperf\Watcher;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\ClassLoader;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Exception\InvalidArgumentException;
use Hyperf\Utils\Filesystem\FileNotFoundException;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Watcher\Driver\DriverInterface;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerInterface;
use Roave\BetterReflection\BetterReflection;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\System;
use Swoole\Process;
use Symfony\Component\Console\Output\OutputInterface;

class Watcher
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Option
     */
    protected $option;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ClassLoader
     */
    protected $loader;

    /**
     * @var array
     */
    protected $autoload;

    /**
     * @var BetterReflection
     */
    protected $reflection;

    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Standard
     */
    protected $printer;

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var string
     */
    protected $path = BASE_PATH . '/runtime/container/collectors.cache';

    public function __construct(ContainerInterface $container, Option $option, OutputInterface $output)
    {
        $this->container = $container;
        $this->option = $option;
        $this->driver = $this->getDriver();
        $this->filesystem = new Filesystem();
        $this->output = $output;
        $json = Json::decode($this->filesystem->get(BASE_PATH . '/composer.json'));
        $this->autoload = array_flip($json['autoload']['psr-4'] ?? []);
        $this->reflection = new BetterReflection();
        $this->reader = new AnnotationReader();
        $this->config = $container->get(ConfigInterface::class);
        $this->printer = new Standard();
        $this->channel = new Channel(1);
        $this->channel->push(true);
    }

    public function run()
    {
        $this->dumpautoload();
        $this->restart(true);

        $channel = new Channel(999);
        Coroutine::create(function () use ($channel) {
            $this->driver->watch($channel);
        });

        $result = [];
        while (true) {
            $file = $channel->pop(0.001);
            if ($file === false) {
                if (count($result) > 0) {
                    $result = [];
                    $this->restart(false);
                }
            } else {
                $ret = System::exec($this->option->getBin() . ' vendor/hyperf/watcher/collector-reload.php ' . $file);
                if ($ret['code'] === 0) {
                    $this->output->writeln('Class reload success.');
                } else {
                    $this->output->writeln('Class reload failed.');
                    $this->output->writeln($ret['output'] ?? '');
                }
                $result[] = $file;
            }
        }
    }

    public function dumpautoload()
    {
        $ret = System::exec('composer dump-autoload -o --no-scripts');
        $this->output->writeln($ret['output'] ?? '');
    }

    public function restart($isStart = true)
    {
        if (! $this->option->isRestart()) {
            return;
        }
        $file = $this->config->get('server.settings.pid_file');
        if (empty($file)) {
            throw new FileNotFoundException('The config of pid_file is not found.');
        }
        $daemonize = $this->config->get('server.settings.daemonize', false);
        if ($daemonize) {
            throw new InvalidArgumentException('Please set `server.settings.daemonize` to false');
        }
        if (! $isStart && $this->filesystem->exists($file)) {
            $pid = $this->filesystem->get($file);
            try {
                $this->output->writeln('Stop server...');
                if (Process::kill((int) $pid, 0)) {
                    Process::kill((int) $pid, SIGTERM);
                }
            } catch (\Throwable $exception) {
                $this->output->writeln('Stop server failed. Please execute `composer dump-autoload -o`');
            }
        }

        Coroutine::create(function () {
            $this->channel->pop();
            $this->output->writeln('Start server ...');

            $descriptorspec = [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ];

            proc_open($this->option->getBin() . ' ' . BASE_PATH . '/vendor/hyperf/watcher/watcher.php start', $descriptorspec, $pipes);

            $this->output->writeln('Stop server success.');
            $this->channel->push(1);
        });
    }

    protected function getDriver()
    {
        $driver = $this->option->getDriver();
        if (! class_exists($driver)) {
            throw new \InvalidArgumentException('Driver not support.');
        }
        return make($driver, ['option' => $this->option]);
    }
}
