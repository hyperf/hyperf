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
namespace Hyperf\Testing\Plugin;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Exceptions\InvalidOption;
use Pest\Kernel;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\Support\Container;
use PHPUnit\TextUI\Application;
use Swoole\Coroutine;
use Swoole\Timer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property string $vendorDir
 */
class Pest implements HandlesArguments
{
    use HandleArguments;

    public function handleArguments(array $arguments): array
    {
        $arguments = $this->prepend($arguments);

        if (Coroutine::getCid() > 0) {
            return $arguments;
        }

        if (! $this->hasArgument('--coroutine', $arguments)) {
            return $arguments;
        }

        if ($this->hasArgument('--parallel', $arguments) || $this->hasArgument('-p', $arguments)) {
            throw new InvalidOption('The coroutine mode is not supported when running in parallel.');
        }

        $arguments = $this->popArgument('--coroutine', $arguments);

        exit($this->runInCoroutine($arguments));
    }

    private function runInCoroutine(array $arguments): int
    {
        $code = 0;
        $output = Container::getInstance()->get(OutputInterface::class);
        $kernel = new Kernel(
            new Application(),
            $output,
        );

        Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL, 'exit_condition' => function () {
            return Coroutine::stats()['coroutine_num'] === 0;
        }]);

        /* @phpstan-ignore-next-line */
        \Swoole\Coroutine\run(function () use (&$code, $kernel, $arguments) {
            $code = $kernel->handle($arguments);
            Timer::clearAll();
            CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        });

        $kernel->shutdown();

        return $code;
    }

    private function prepend(array $arguments): array
    {
        $prepend = null;
        foreach ($arguments as $key => $argument) {
            if (str_starts_with($argument, '--prepend=')) {
                $prepend = explode('=', $argument, 2)[1];
                unset($arguments[$key]);
                break;
            }
            if (str_starts_with($argument, '--prepend')) {
                if (isset($arguments[$key + 1])) {
                    $prepend = $arguments[$key + 1];
                    unset($arguments[$key + 1]);
                }
                unset($arguments[$key]);
            }
        }

        if ($prepend && file_exists($prepend)) {
            require_once $prepend;
        }

        return $arguments;
    }
}
