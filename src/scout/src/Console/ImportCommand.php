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

namespace Hyperf\Scout\Console;

use Hyperf\Command\Command;
use Hyperf\Context\ApplicationContext;
use Hyperf\Scout\Event\ModelsImported;
use Psr\EventDispatcher\ListenerProviderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected ?string $name = 'scout:import';

    /**
     * The console command description.
     */
    protected string $description = 'Import the given model into the search index';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        define('SCOUT_COMMAND', true);
        $class = $this->input->getArgument('model');
        $chunk = (int) $this->input->getOption('chunk');
        $column = (string) $this->input->getOption('column');
        $model = new $class();
        $provider = ApplicationContext::getContainer()->get(ListenerProviderInterface::class);
        $provider->on(ModelsImported::class, function ($event) use ($class) {
            /** @var ModelsImported $event */
            $key = $event->models->last()->getScoutKey(); // @phpstan-ignore-line
            $this->line('<comment>Imported [' . $class . '] models up to ID:</comment> ' . $key);
        });
        $model::makeAllSearchable($chunk ?: null, $column ?: null);
        $this->info('All [' . $class . '] records have been imported.');
    }

    protected function getOptions()
    {
        return [
            ['column', 'c', InputOption::VALUE_OPTIONAL, 'Column used in chunking. (Default use primary key)'],
            ['chunk', '', InputOption::VALUE_OPTIONAL, 'The number of records to import at a time (Defaults to configuration value: `scout.chunk.searchable`)'],
        ];
    }

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'fully qualified class name of the model'],
        ];
    }
}
