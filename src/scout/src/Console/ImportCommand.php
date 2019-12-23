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

namespace Hyperf\Scout\Console;

use Hyperf\Command\Command;
use Hyperf\Event\ListenerProvider;
use Hyperf\Scout\Event\ModelsImported;
use Hyperf\Utils\ApplicationContext;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'scout:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the given model into the search index';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $class = $this->input->getArgument('model');
        $model = new $class();
        $provider = ApplicationContext::getContainer()->get(ListenerProvider::class);
        $provider->on(ModelsImported::class, function ($event) use ($class) {
            $key = $event->models->last()->getScoutKey();
            $this->line('<comment>Imported [' . $class . '] models up to ID:</comment> ' . $key);
        });
        $model::makeAllSearchable();
        $this->info('All [' . $class . '] records have been imported.');
    }

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'fully qualified class name of the model'],
        ];
    }
}
