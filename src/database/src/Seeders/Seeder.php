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
namespace Hyperf\Database\Seeders;

use Hyperf\Contract\ApplicationInterface;
use Hyperf\Utils\ApplicationContext;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class Seeder
{
    /**
     * Enables, if supported, wrapping the seeder within a transaction.
     *
     * @var bool
     */
    public $withinTransaction = true;

    /**
     * The name of the database connection to use.
     *
     * @var null|string
     */
    protected $connection = 'default';

    /**
     * Get the seeder connection name.
     *
     * @return null|string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    protected function call(array $classes)
    {
        foreach ($classes as $class) {
            $this->runCommand($class);
        }
    }

    protected function runCommand($class)
    {
        $command = 'db:seed';
        $params = ["command" => $command, "--path" =>  'seeders/' . $class . '.php'];

        $input = new ArrayInput($params);
        $output = new NullOutput();

        /** @var Application $application */
        $application = ApplicationContext::getContainer()->get(ApplicationInterface::class);
        $application->setAutoExit(false);
        $application->find($command)->run($input, $output);
    }
}
