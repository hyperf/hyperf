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

namespace Hyperf\Devtool\Describe;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Event\ListenerData;
use Hyperf\Event\ListenerProvider;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[Command]
class ListenersCommand extends HyperfCommand
{
    public function __construct(private ContainerInterface $container)
    {
        parent::__construct('describe:listeners');
    }

    public function handle()
    {
        $events = $this->input->getOption('events');
        $events = $events ? explode(',', $events) : null;
        $listeners = $this->input->getOption('listeners');
        $listeners = $listeners ? explode(',', $listeners) : null;

        $provider = $this->container->get(ListenerProviderInterface::class);
        $this->show($this->handleData($provider, $events, $listeners), $this->output);
    }

    protected function configure()
    {
        $this->setDescription('Describe the events and listeners.')
            ->addOption('events', 'e', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified information by events.')
            ->addOption('listeners', 'l', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified information by listeners.');
    }

    protected function handleData(ListenerProviderInterface $provider, ?array $events, ?array $listeners): array
    {
        $data = [];
        if (! $provider instanceof ListenerProvider) {
            return $data;
        }
        foreach ($provider->listeners as $listener) {
            if ($listener instanceof ListenerData) {
                $event = $listener->event;
                if (! is_array($listener->listener)) {
                    continue;
                }
                [$object, $method] = $listener->listener;
                $listenerClassName = get_class($object);
                if ($events && ! $this->isMatch($event, $events)) {
                    continue;
                }
                if ($listeners && ! $this->isMatch($listenerClassName, $listeners)) {
                    continue;
                }
                $data[$event]['events'] = $listener->event;
                $data[$event]['listeners'] = array_merge($data[$event]['listeners'] ?? [], [
                    implode('::', [
                        $listenerClassName,
                        $method,
                    ]),
                ]);
            }
        }
        return $data;
    }

    protected function isMatch(string $target, array $keywords = [])
    {
        foreach ($keywords as $keyword) {
            if (str_contains($target, $keyword)) {
                return true;
            }
        }
        return false;
    }

    protected function show(array $data, OutputInterface $output)
    {
        $rows = [];
        foreach ($data as $route) {
            $route['listeners'] = implode(PHP_EOL, (array) $route['listeners']);
            $rows[] = $route;
            $rows[] = new TableSeparator();
        }
        $rows = array_slice($rows, 0, count($rows) - 1);
        $table = new Table($output);
        $table->setHeaders(['Events', 'Listeners'])->setRows($rows);
        $table->render();
    }
}
