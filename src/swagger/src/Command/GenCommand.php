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
namespace Hyperf\Swagger\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Swagger\Generator;
use Psr\Container\ContainerInterface;

class GenCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('gen:swagger');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generate swagger json file.');
    }

    public function handle()
    {
        $generator = $this->container->get(Generator::class);

        $generator->generate();

        $this->output->writeln('Generate swagger json success.');
    }
}
