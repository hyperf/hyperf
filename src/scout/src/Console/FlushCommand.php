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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class FlushCommand extends HyperfCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'scout:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Flush all of the model's records from the index";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        define('SCOUT_COMMAND', true);
        $class = $this->input->getArgument('model');
        $model = new $class();
        $model::removeAllFromSearch();
        $this->info('All [' . $class . '] records have been flushed.');
    }

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'fully qualified class name of the model'],
        ];
    }
}
