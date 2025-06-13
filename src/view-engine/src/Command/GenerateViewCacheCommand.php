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

namespace Hyperf\ViewEngine\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\ViewEngine\Compiler\CompilerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Finder\Finder;

class GenerateViewCacheCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('gen:view-engine-cache');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generate View Engine Cache');
    }

    public function handle()
    {
        $dir = $this->container->get(ConfigInterface::class)->get('view.config.view_path');
        if (empty($dir)) {
            $this->output->warning('Please set config `view.config.view_path`.');
            return;
        }

        $finder = Finder::create()->in($dir)->files()->name('*.blade.php');
        $compiler = $this->container->get(CompilerInterface::class);
        foreach ($finder as $item) {
            $compiler->compile($item->getRealPath());
            $this->output->writeln(sprintf('File `%s` cache generation completed', $item->getRealPath()));
        }
    }
}
