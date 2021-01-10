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
use Hyperf\Phar\Ast\Ast;
use Hyperf\Phar\Ast\Visitor\RewriteConfigVisitor;
use InvalidArgumentException;
use Phar;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Coroutine\System;
use Symfony\Component\Finder\Finder;
use UnexpectedValueException;

class PharBuilder
{
    /**
     * @var LoggerInterface
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
     * @var null|bool
     */
    private $noDev;


    /**
     * @var null|array
     */
    private $exclude;

    /**
     * @var array
     */
    private $composerCmd = [];


    /**
     * @var string
     */
    private $main;

    public function __construct(string $path, LoggerInterface $logger, string $exclude = "")
    {
        $this->logger = $logger;
        $this->exclude = explode(',', $exclude) ?? [];
        $this->package = new Package($this->loadJson($path), dirname(realpath($path)), $this->exclude);
    }

    /**
     * Gets the Phar package name.
     */
    public function getTarget(): string
    {
        if ($this->target === null) {
            $this->target = $this->package->getShortName() . '.phar';
        }
        return (string)$this->target;
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
     * Set the Phar package is dev.
     * @param bool $noDev
     * @return $this
     */
    public function setNoDev(bool $noDev)
    {
        $this->noDev = $noDev;
        return $this;
    }

    /**
     * Set the composer cmd.
     * @param string $composerCmd
     * @return $this
     */
    public function setComposerCmd(string $composerCmd)
    {
        $this->composerCmd = explode(',', $composerCmd) ?? [];
        return $this;
    }

    /**
     * Gets the default run script path.
     */
    public function getMain(): string
    {
        if ($this->main === null) {
            foreach ($this->package->getBins() as $path) {
                if (!file_exists($this->package->getDirectory() . $path)) {
                    throw new UnexpectedValueException('Bin file "' . $path . '" does not exist');
                }
                $this->main = $path;
                break;
            }
            // Use the default hyperf bootstrap file as default.
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
     */
    public function getPackage(): Package
    {
        return $this->package;
    }

    /**
     * Gets a list of all dependent packages.
     * @return Package[]
     */
    public function getPackagesDependencies(): array
    {
        $packages = [];

        $vendorPath = $this->package->getVendorAbsolutePath();

        // Gets all installed dependency packages
        if (is_file($vendorPath . 'composer/installed.json')) {
            $installed = $this->loadJson($vendorPath . 'composer/installed.json');
            $installedPackages = $installed;
            // Adapte Composer 2.0
            if (isset($installed['packages'])) {
                $installedPackages = $installed['packages'];
            }
            // Package all of these dependent components into the packages
            foreach ($installedPackages as $package) {
                $dir = $package['install-path'] . '/';

                if (isset($package['target-dir'])) {
                    $dir .= trim($package['target-dir'], '/') . '/';
                }

                $dir = $vendorPath . 'composer/' . $dir;

                $packages[] = new Package($package, $this->canonicalize($dir), $this->exclude);

            }
        }
        return $packages;
    }

    /**
     * Gets the canonicalize path .
     */
    function canonicalize($address)
    {
        $address = explode('/', $address);
        $keys = array_keys($address, '..');

        foreach ($keys as $keypos => $key) {
            array_splice($address, $key - ($keypos * 2 + 1), 2);
        }

        $address = implode('/', $address);
        $address = str_replace('./', '', $address);

        return $address;
    }

    /**
     * Gets the relative path relative to the resource bundle.
     */
    public function getPathLocalToBase(string $path): ?string
    {
        $root = $this->package->getDirectory();
        if (strpos($path, $root) !== 0) {
            throw new UnexpectedValueException('Path "' . $path . '" is not within base project path "' . $root . '"');
        }
        return substr($path, strlen($root)) ?? null;
    }

    /**
     * exec composr cmd.
     */
    public function execComposr(string $cmd): bool
    {
        foreach ($this->composerCmd as $composer) {
            $return = System::exec("{$composer} $cmd");
            if (($return['code'] ?? -1) === 0) {
                return true;
            }
        }
        throw new UnexpectedValueException('composer is not install ,try "' . implode(',', $this->composerCmd) . '"');

    }

    /**
     * Compile the code into the Phar file.
     */
    public function build()
    {
        $this->logger->info('Creating phar <info>' . $this->getTarget() . '</info>');
        $time = microtime(true);

        $vendorPath = $this->package->getVendorAbsolutePath();
        if (! is_dir($vendorPath)) {
            throw new RuntimeException(sprintf('Directory %s not properly installed, did you run "composer install" ?', $vendorPath));
        }

        // Get file path which could be written for phar.
        $target = $this->getTarget();
        do {
            $tmp = $target . '.' . mt_rand() . '.phar';
        } while (file_exists($tmp));

        $targetPhar = new TargetPhar(new Phar($tmp), $this);
        $this->logger->info('Adding main package "' . $this->package->getName() . '"');
        $finder = Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->exclude(rtrim($this->package->getVendorPath(), '/'))
            ->exclude('runtime') //Ignore runtime dir
            ->exclude($this->exclude)
            ->exclude('composer.lock')
            ->notPath('/^composer\.phar/')
            ->notPath($target) //Ignore the phar package that exists in the project itself
            ->in($this->package->getDirectory());

        $targetPhar->addBundle($this->package->bundle($finder));

        // Force to turn on ScanCacheable.
        $this->enableScanCacheable($targetPhar);

        // Load the Runtime folder separately
        if (is_dir($this->package->getDirectory() . 'runtime')) {
            $this->logger->info('Adding runtime container files');
            $finder = Finder::create()
                ->files()
                ->in($this->package->getDirectory() . 'runtime/container');
            $targetPhar->addBundle($this->package->bundle($finder));
        }


        // Read composer.lock
        $lockPath = '';
        if (is_readable(BASE_PATH . '/composer.lock')) {
            $lockPath = BASE_PATH . '/composer.lock';
        } else {
            throw new \RuntimeException('composer.lock not found.');
        }
        $lock = json_decode(file_get_contents($lockPath), true);
        //Setting no dev
        $packagesList = [];
        if ($this->noDev) {
            $this->logger->info('Setting composer no-dev');
            foreach ($lock['packages'] ?? [] as $package) {
                $packagesList[$package['name'] ?? ''] = true;
            }
            //delete dev autoload
            $this->execComposr("dumpautoload --no-dev -o");

            //delete dev packages
            $lock['packages-dev'] = [];
        }
        // Add composer.lock
        $targetPhar->addFromString('composer.lock', json_encode($lock, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->logger->info('Adding composer base files');
        // Add composer autoload file.
        $targetPhar->addFile($vendorPath . 'autoload.php');

        // Add composer autoload files.
        $targetPhar->buildFromIterator(new GlobIterator($vendorPath . 'composer/*.*', FilesystemIterator::KEY_AS_FILENAME));
        if ($this->noDev) {
            //restore dev autoload
            $this->execComposr("dumpautoload");

        }
        // Add composer depenedencies.
        foreach ($this->getPackagesDependencies() as $package) {
            // Cannot dev package .
            if ($this->noDev && empty($packagesList[$package->getName()])) {
                continue;
            }
            $this->logger->info('Adding dependency "' . $package->getName() . '" from "' . $this->getPathLocalToBase($package->getDirectory()) . '"');
            if (is_link(rtrim($package->getDirectory(), '/'))) {
                $targetPhar->buildFromLinkIterator($package->bundle());
            } else {
                $targetPhar->addBundle($package->bundle());
            }
        }

        $this->logger->info('Setting main/stub');

        $main = $this->getMain();
        // Add the default stub.
        $targetPhar->setStub($targetPhar->createDefaultStub($main));
        $this->logger->info('Setting default stub <info>' . $main . '</info>.');

        $targetPhar->stopBuffering();

        if (file_exists($target)) {
            $this->logger->info('Overwriting existing file <info>' . $target . '</info> (' . $this->getSize($target) . ')');
        }

        if (rename($tmp, $target) === false) {
            throw new UnexpectedValueException(sprintf('Unable to rename temporary phar archive to %s', $target));
        }

        $time = max(microtime(true) - $time, 0);

        $this->logger->info('');
        $this->logger->info('    <info>OK</info> - Creating <info>' . $this->getTarget() . '</info> (' . $this->getSize($this->getTarget()) . ') completed after ' . round($time, 1) . 's');
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
        $result = json_decode(file_get_contents($path), true);
        if ($result === null) {
            throw new InvalidArgumentException(sprintf('Unable to parse given path %s', $path), json_last_error());
        }
        return $result;
    }

    /**
     * Get file size.
     *
     * @param PharBuilder|string $path
     */
    private function getSize($path): string
    {
        return round(filesize((string)$path) / 1024, 1) . ' KiB';
    }
}
