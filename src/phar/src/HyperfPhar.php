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

use FilesystemIterator;
use GlobIterator;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Phar\Ast\Ast;
use Hyperf\Phar\Ast\Visitor\RewriteConfigVisitor;
use InvalidArgumentException;
use Phar;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use UnexpectedValueException;

class HyperfPhar
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var null|string|TargetPhar
     */
    private $target;

    /**
     * @var string
     */
    private $main;

    public function __construct(ContainerInterface $container, string $path)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->package = new Package($this->loadJson($path), dirname(realpath($path)));
    }

    /**
     * Gets the Phar package name.
     * @return string|TargetPhar
     */
    public function getTarget()
    {
        if ($this->target === null) {
            $this->target = $this->package->getShortName() . '.phar';
        }
        return $this->target;
    }

    /**
     * Set the Phar package name.
     * @param string|TargetPhar $target
     * @return $this
     */
    public function setTarget($target)
    {
        if (is_dir($target)) {
            $this->target = null;
            $target = rtrim($target, '/') . '/' . $this->getTarget();
        }
        $this->target = $target;
        return $this;
    }

    /**
     * Gets the default run script path.
     */
    public function getMain(): string
    {
        if ($this->main === null) {
            foreach ($this->package->getBins() as $path) {
                if (! file_exists($this->package->getDirectory() . $path)) {
                    throw new UnexpectedValueException('Bin file "' . $path . '" does not exist');
                }
                $this->main = $path;
                break;
            }
            //For the bottom, use the hyperF default startup file
            if ($this->main == null) {
                return 'bin/hyperf.php';
            }
        }
        return $this->main;
    }

    /**
     * Set the default startup file.
     * @return $this
     */
    public function setMain(string $main)
    {
        $this->main = $main;
        return $this;
    }

    /**
     * Get package object.
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Gets a list of all dependent packages.
     */
    public function getPackagesDependencies(): array
    {
        $packages = [];

        $pathVendor = $this->package->getDirectory() . $this->package->getPathVendor();

        //  Gets all installed dependency packages
        if (is_file($pathVendor . 'composer/installed.json')) {
            $installed = $this->loadJson($pathVendor . 'composer/installed.json');
            $installedPackages = $installed;
            //Configuration structure changes that support Composer 2.0
            if (isset($installed['packages'])) {
                $installedPackages = $installed['packages'];
            }
            //Package all of these dependent components into packages
            foreach ($installedPackages as $package) {
                $dir = $package['name'] . '/';
                if (isset($package['target-dir'])) {
                    $dir .= trim($package['target-dir'], '/') . '/';
                }

                $dir = $pathVendor . $dir;
                $packages[] = new Package($package, $dir);
            }
        }
        return $packages;
    }

    /**
     * Gets the relative path relative to the resource bundle.
     * @return false|string
     */
    public function getPathLocalToBase(string $path)
    {
        $root = $this->package->getDirectory();
        if (strpos($path, $root) !== 0) {
            throw new UnexpectedValueException('Path "' . $path . '" is not within base project path "' . $root . '"');
        }
        return substr($path, strlen($root));
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        $this->logger->info($message);
    }

    /**
     * Compile the code into the Phar file.
     */
    public function build()
    {
        $this->log('Creating phar <info>' . $this->getTarget() . '</info>');
        $time = microtime(true);

        // Assert vendor dir must exists.
        $pathVendor = $this->package->getDirectory() . $this->package->getPathVendor();
        if (! is_dir($pathVendor)) {
            throw new RuntimeException('Directory "' . $pathVendor . '" not properly installed, did you run "composer install"?');
        }

        // Get file path which could be written for phar.
        $target = $this->getTarget();
        do {
            $tmp = $target . '.' . mt_rand() . '.phar';
        } while (file_exists($tmp));

        $targetPhar = new TargetPhar(new Phar($tmp), $this);
        $this->log('  - Adding main package "' . $this->package->getName() . '"');
        // Add project self.
        $finder = Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->exclude(rtrim($this->package->getPathVendor(), '/'))
            ->exclude('runtime') //Ignore runtime dir
            ->notPath('/^composer\.phar/')
            ->notPath($target) //Ignore the phar package that exists in the project itself
            ->in($this->package->getDirectory());
        $targetPhar->addBundle($this->package->bundle($finder));

        // Force the ScanCacheable switch on
        $this->enableScanCacheable($targetPhar);

        //Load the Runtime folder separately
        if (is_dir($this->package->getDirectory() . 'runtime')) {
            $this->log('  - Adding runtime container files');
            $finder = Finder::create()
                ->files()
                ->in($this->package->getDirectory() . 'runtime/container');
            $targetPhar->addBundle($this->package->bundle($finder));
        }

        $this->log('  - Adding composer base files');
        // Add autoload.php
        $targetPhar->addFile($pathVendor . 'autoload.php');

        // Add composer autoload files.
        $targetPhar->buildFromIterator(new GlobIterator($pathVendor . 'composer/*.*', FilesystemIterator::KEY_AS_FILENAME));

        // Add composer depenedencies.
        foreach ($this->getPackagesDependencies() as $package) {
            //You don't have to package yourself
            if (stripos($package->getDirectory(), 'vendor/hyperf/phar/') !== false) {
                continue;
            }
            $this->log('  - Adding dependency "' . $package->getName() . '" from "' . $this->getPathLocalToBase($package->getDirectory()) . '"');
            $targetPhar->addBundle($package->bundle());
        }

        $this->log('  - Setting main/stub');

        $main = $this->getMain();
        // Add the default stub.
        $targetPhar->setStub($targetPhar->createDefaultStub($main));
        $this->log('  - Setting default stub <info>' . $main . '</info>.');

        $targetPhar->stopBuffering();

        if (file_exists($target)) {
            $this->log('  - Overwriting existing file <info>' . $target . '</info> (' . $this->getSize($target) . ')');
        }

        if (rename($tmp, $target) === false) {
            throw new UnexpectedValueException('Unable to rename temporary phar archive to "' . $target . '"');
        }

        $time = max(microtime(true) - $time, 0);

        $this->log('');
        $this->log('    <info>OK</info> - Creating <info>' . $this->getTarget() . '</info> (' . $this->getSize($this->getTarget()) . ') completed after ' . round($time, 1) . 's');
    }

    /**
     * Find the scan_cacheable configuration and force it to open.
     */
    protected function enableScanCacheable(TargetPhar $targetPhar)
    {
        $configPath = 'config/config.php';
        $absPath = $this->package->getDirectory() . $configPath;
        if (! file_exists($absPath)) {
            return;
        }
        $code = file_get_contents($absPath);
        $code = Ast::parse($code, [new RewriteConfigVisitor()]);
        $targetPhar->addFromString($configPath, $code);
    }

    /**
     * Load the configuration.
     */
    private function loadJson(string $path): array
    {
        $ret = json_decode(file_get_contents($path), true);
        if ($ret === null) {
            throw new InvalidArgumentException('Unable to parse given path "' . $path . '"', json_last_error());
        }
        return $ret;
    }

    /**
     * Get file size.
     * @param HyperfPhar|string $path
     * @return string
     */
    private function getSize($path)
    {
        return round(filesize((string) $path) / 1024, 1) . ' KiB';
    }
}
