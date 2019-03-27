<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Devtool;

use Hyperf\Config\ProviderConfig;
use Hyperf\Framework\Annotation\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class VendorPublishCommand extends SymfonyCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var bool
     */
    protected $force = false;

    public function __construct()
    {
        parent::__construct('vendor:publish');
    }

    protected function configure()
    {
        $this->setDescription('Publish any publishable configs from vendor packages.')
            ->addArgument('package', InputArgument::OPTIONAL, 'The package config you want to publish.')
            ->addOption('show', 's', InputOption::VALUE_OPTIONAL, 'Show all packages can be publish.', false)
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'Overwrite any existing files', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->force = $input->getOption('force') !== false;
        $package = $input->getArgument('package');
        $show = $input->getOption('show') !== false;

        $configs = ProviderConfig::load()['configs'];

        if ($show) {
            foreach ($configs as $group => $config) {
                $output->writeln(sprintf('<fg=green>Package[%s] can be publish.</>', $group));
            }
            return;
        }

        if ($package) {
            if (! isset($configs[$package])) {
                return $output->writeln(sprintf('<fg=red>Config of package[%s] is not exist.</>', $package));
            }

            return $this->copy($configs[$package], $package);
        }

        foreach ($configs as $group => $config) {
            $this->copy($config, $group);
        }
    }

    protected function copy($configs, $package)
    {
        foreach ($configs as $origin => $target) {
            if (! $this->force && file_exists($target)) {
                return $this->output->writeln(sprintf('<fg=red>Config[%s] is exist.</>', $target));
            }

            @copy($origin, $target);

            $this->output->writeln(sprintf('<fg=green>Copy config[%s] success.</>', pathinfo($target)['basename']));
        }

        return $this->output->writeln(sprintf('<fg=green>Packagep[%s] publish success.</>' . PHP_EOL, $package));
    }
}
