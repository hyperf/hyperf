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

use Hyperf\Codec\Json;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Hyperf\Support\Exception\InvalidArgumentException;
use Hyperf\Support\Filesystem\FileNotFoundException;
use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\Watcher\Driver\DriverInterface;
use Hyperf\Watcher\Event\BeforeServerRestart;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function Hyperf\Support\make;

class Watcher
{
    protected DriverInterface $driver;

    protected Filesystem $filesystem;

    protected array $autoload;

    protected ConfigInterface $config;

    protected Standard $printer;

    protected Channel $channel;

    protected string $path = BASE_PATH . '/runtime/container/collectors.cache';

    public function __construct(protected ContainerInterface $container, protected Option $option, protected OutputInterface $output)
    {
        $this->driver = $this->getDriver();
        $this->filesystem = new Filesystem();
        $json = Json::decode($this->filesystem->get(BASE_PATH . '/composer.json'));
        $this->autoload = array_flip($json['autoload']['psr-4'] ?? []);
        $this->config = $container->get(ConfigInterface::class);
        $this->printer = new Standard();
        $this->channel = new Channel(1);
        $this->channel->push(true);
    }

    public function run()
    {
        $this->dumpAutoload();
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
                $ret = exec(sprintf('%s %s/vendor/hyperf/watcher/collector-reload.php %s', $this->option->getBin(), BASE_PATH, $file));
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

    public function dumpAutoload()
    {
        $ret = exec('composer dump-autoload -o --no-scripts -d ' . BASE_PATH);
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
                $this->container->get(EventDispatcherInterface::class)
                    ->dispatch(new BeforeServerRestart($pid));
                if (posix_kill((int) $pid, 0)) {
                    posix_kill((int) $pid, SIGTERM);
                }
            } catch (Throwable) {
                $this->output->writeln('Stop server failed. Please execute `composer dump-autoload -o`');
            }
        }

        Coroutine::create(function () {
            $this->channel->pop();
            $this->output->writeln('Start server ...');

            $descriptorSpec = [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ];

            proc_open(
                command: $this->option->getBin() . ' ' . BASE_PATH . '/' . $this->option->getCommand(),
                descriptor_spec: $descriptorSpec,
                pipes: $pipes
            );

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
