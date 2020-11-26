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
namespace Hyperf\Phar;

use Hyperf\Command\Command as HyperfCommand;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use UnexpectedValueException;

class BuildCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * BuildCommand constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct('phar:build');
        $this->container = $container;
    }

    /**
     * tips
     */
    public function configure()
    {
        $this->setDescription('Pack your project into a Phar package.')
            ->addOption('name', '', InputOption::VALUE_OPTIONAL, 'This is the name of the Phar package, and if it is not passed in, the project name is used by default', null)
            ->addOption('bin', 'b', InputOption::VALUE_OPTIONAL, 'The script path to execute by default.', 'bin/hyperf.php')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Project root path, default BASE_PATH.', null);
    }

    /**
     * run command
     */
    public function handle()
    {
        $this->assertWritable();
        $name = $this->input->getOption('name');
        $bin = $this->input->getOption('bin');
        $path = $this->input->getOption('path');
        if (empty($path)) {
            $path = BASE_PATH;
        }
        $phar = $this->getPhar($path);
        if (! empty($bin)) {
            $phar->setMain($bin);
        }
        if (! empty($name)) {
            $phar->setTarget($name);
        }
        $phar->build();
    }

    /**
     * check readonly
     */
    public function assertWritable()
    {
        if (ini_get('phar.readonly') === '1') {
            throw new UnexpectedValueException('Your configuration disabled writing phar files (phar.readonly = On), please update your configuration');
        }
    }

    /**
     * @param $path
     * @return HyperfPhar
     */
    public function getPhar($path, ?string $version = null)
    {
        if ($version !== null) {
            $path .= ':' . $version;
        }

        if (is_dir($path)) {
            $path = rtrim($path, '/') . '/composer.json';
        }
        if (! is_file($path)) {
            throw new InvalidArgumentException('The given path "' . $path . '" is not a readable file');
        }
        $phar = new HyperfPhar($this->container, $path);

        $pathVendor = $phar->getPackage()->getDirectory() . $phar->getPackage()->getPathVendor();
        if (! is_dir($pathVendor)) {
            throw new RuntimeException('Project is not installed via composer. Run "composer install" manually');
        }
        return $phar;
    }
}
