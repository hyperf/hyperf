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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class DropCommand extends HyperfCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'scout:drop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remove elasticsearch index mapping';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        define('SCOUT_COMMAND', true);
        $class = $this->input->getArgument('model');
        $model = new $class();
        $model->searchableDropStruct();
        $this->info(' [' . $class . '] index has been deleted');
    }

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'fully qualified class name of the model'],
        ];
    }
}
