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
namespace Hyperf\Watcher\Command;

use Hyperf\Command\Command;
use Hyperf\Command\NullDisableEventDispatcher;
use Hyperf\Watcher\Option;
use Hyperf\Watcher\Watcher;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

use function Hyperf\Support\make;

class WatchCommand extends Command
{
    use NullDisableEventDispatcher;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('server:watch');
        $this->setDescription('watch command');
        $this->addOption('config', 'C', InputOption::VALUE_OPTIONAL, '', '.watcher.php');
        $this->addOption('file', 'F', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', []);
        $this->addOption('dir', 'D', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', []);
        $this->addOption('no-restart', 'N', InputOption::VALUE_NONE, 'Whether no need to restart server');
    }

    public function handle()
    {
        $options = (array) include dirname(__DIR__, 2) . '/publish/watcher.php';

        if (file_exists($configFile = $this->input->getOption('config'))) {
            $options = array_replace($options, (array) include $configFile);
        } elseif (file_exists($configFile = BASE_PATH . '/config/autoload/watcher.php')) { // Compatible with old version, will be removed in the v3.1.
            $options = array_replace($options, (array) include $configFile);
        }

        $option = make(Option::class, [
            'options' => $options,
            'dir' => $this->input->getOption('dir'),
            'file' => $this->input->getOption('file'),
            'restart' => ! $this->input->getOption('no-restart'),
        ]);

        $watcher = make(Watcher::class, [
            'option' => $option,
            'output' => $this->output,
        ]);

        $watcher->run();
    }
}
