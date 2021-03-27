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
use Hyperf\Phar\Ast\Visitor\RewriteConfigFactoryVisitor;
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
     * @var array
     */
    private $mount = [];

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $main;

    /**
     * @var null|bool
     */
    private $noDev;

    /**
     * @var array
     */
    private $composer = ['composer', 'composer.phar', './composer', './composer.phar'];

    /**
     * @var null|array
     */
    private $exclude;

    public function __construct(string $path, LoggerInterface $logger, array $exclude = [])
    {
        $this->logger = $logger;
        $this->exclude = $exclude;
        $this->package = new Package($this->loadJson($path), dirname(realpath($path)), $this->exclude);
    }

    /**
     * Gets the Phar package name.
     */
    public function getTarget(): string
    {
        if ($this->target === null) {
            $target = $this->package->getShortName();
            if ($this->version !== null) {
                $target .= ':' . $this->version;
            }
            $this->target = $target . '.phar';
        }
        return (string) $this->target;
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
     * @return $this
     */
    public function setVersion(string $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Set the Phar package is dev.
     * @return $this
     */
    public function setNoDev(bool $noDev)
    {
        $this->noDev = $noDev;
        return $this;
    }

    /**
     * Set the composer cmd.
     * @return $this
     */
    public function setComposer(array $composer)
    {
        $this->composer = $composer;
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
     * @return $this
     */
    public function setMount(array $mount = [])
    {
        foreach ($mount as $item) {
            $items = explode(':', $item);
            $this->mount[$items[0]] = $items[1] ?? $items[0];
        }

        return $this;
    }

    public function getMount(): array
    {
        return $this->mount;
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
                // support custom install path
                $dir = 'composer/' . $package['install-path'] . '/';
                if (isset($package['target-dir'])) {
                    $dir .= trim($package['target-dir'], '/') . '/';
                }

                $dir = $vendorPath . $dir;

                $packages[] = new Package($package, $this->canonicalize($dir), $this->exclude);
            }
        }
        return $packages;
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
        $base = substr($path, strlen($root));
        return empty($base) ? null : $this->canonicalize($base);
    }

    /**
     * Gets the canonicalize path, like realpath.
     * @param mixed $address
     */
    public function canonicalize($address)
    {
        $address = explode('/', $address);
        $keys = array_keys($address, '..');

        foreach ($keys as $keypos => $key) {
            array_splice($address, $key - ($keypos * 2 + 1), 2);
        }

        $address = implode('/', $address);
        return str_replace('./', '', $address);
    }

    /**
     * exec composr cmd.
     */
    public function execComposr(string $cmd): bool
    {
        foreach ($this->composer as $composer) {
            $return = System::exec("{$composer} {$cmd}");
            if (($return['code'] ?? -1) === 0) {
                return true;
            }
        }
        throw new UnexpectedValueException('composer is not install ,try "' . implode(',', $this->composer) . '"');
    }

    /**
     * Compile the code into the Phar file.
     */
    public function getMountLinkCode(): string
    {
        $mountString = '';
        foreach ($this->getMount() as $link => $inside) {
            $mountString .= "'{$link}' => '{$inside}',";
        }

        return <<<EOD
<?php
\$mount = [{$mountString}];
\$path = dirname(realpath(\$argv[0]));
array_walk(\$mount, function (\$item, \$link) use (\$path) {
    \$file = \$link;
    if(ltrim(\$link, '/') == \$link){
        \$file = \$path . '/' . \$link;   
    }
    if(!file_exists(\$file)){
        if(rtrim(\$item, '/')!=\$item){
            @mkdir(\$file, 0777, true);
        }else{
            file_exists(dirname(\$file)) || @mkdir(dirname(\$file), 0777, true);
            file_put_contents(\$file,"");
        }
    }
    Phar::mount(\$item,\$file);
});
EOD;
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

        $main = $this->getMain();

        $targetPhar = new TargetPhar(new Phar($tmp), $this);
        $this->logger->info('Adding main package "' . $this->package->getName() . '"');
        $finder = Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->exclude(rtrim($this->package->getVendorPath(), '/'))
            ->exclude('runtime') // Ignore runtime dir
            ->notPath('/^composer\.phar/')
            ->exclude($main)
            ->exclude($this->exclude)
            ->exclude('composer.lock')
            ->notPath($target); // Ignore the phar package that exists in the project itself

        foreach ($this->getMount() as $inside) {
            $finder = $finder->exclude($inside);
        }

        $finder = $finder->in($this->package->getDirectory());

        $targetPhar->addBundle($this->package->bundle($finder));

        // Force to turn on ScanCacheable.
        $this->enableScanCacheable($targetPhar);


        // Add .env file.
        if (! in_array('.env', $this->getMount()) && is_file($this->package->getDirectory() . '.env')) {
            $this->logger->info('Adding .env file');
            $targetPhar->addFile($this->package->getDirectory() . '.env');
        }

        $this->logger->info('Adding composer base files');


        // Read composer.lock
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

            //delete dev packages
            $lock['packages-dev'] = [];

            //delete dev autoload
            $bashVendorPath = $this->getPathLocalToBase($vendorPath);
            $tmpPharDir = $this->package->getDirectory() . 'runtime/build_tmp/';
            try {
                System::exec("rm -rf {$tmpPharDir}");

                mkdir($tmpPharDir, 0777, true);

                // Add base file
                foreach (new GlobIterator($this->package->getDirectory() . '*', FilesystemIterator::KEY_AS_FILENAME) as $cFile) {
                    if (!in_array($cFile->getFilename(), ['runtime'])) {
                        $t = $tmpPharDir . $cFile->getFilename();
                        System::exec("cp -r {$cFile->getPathname()} {$t}");
                    }
                }
                $this->execComposr("dump-autoload --no-dev -o -d {$tmpPharDir} ");
                System::exec("php {$tmpPharDir}/bin/hyperf.php show:name -N hyperf-phar-tester");

                $this->logger->info('Adding no dev composer base files');
                // Add no dev composer autoload file.
                $targetPhar->addFromString($bashVendorPath . 'autoload.php', file_get_contents($tmpPharDir . $bashVendorPath . 'autoload.php'));

                // Add no dev composer autoload files.
                foreach (new GlobIterator($tmpPharDir . $bashVendorPath . 'composer/*.*', FilesystemIterator::KEY_AS_FILENAME) as $cFile) {
                    $targetPhar->addFromString($bashVendorPath . 'composer/' . $cFile->getFilename(), file_get_contents($cFile->getPathname()));
                }

                // Load the Runtime folder separately
                if (is_dir($tmpPharDir . 'runtime')) {
                    $this->logger->info('Adding runtime container files');
                    // Add no dev container cache files.
                    foreach (new GlobIterator($tmpPharDir . 'runtime/container/*', FilesystemIterator::KEY_AS_FILENAME) as $cFile) {
                        $targetPhar->addFromString('runtime/container/' . $cFile->getFilename(), file_get_contents($cFile->getPathname()));
                    }
                }
            } finally {
                System::exec("rm -rf ${tmpPharDir}");
            }
        } else {
            // Add composer autoload file.
            $targetPhar->addFile($vendorPath . 'autoload.php');

            // Add composer autoload files.
            $targetPhar->buildFromIterator(new GlobIterator($vendorPath . 'composer/*.*', FilesystemIterator::KEY_AS_FILENAME));

            // Load the Runtime folder separately
            if (is_dir($this->package->getDirectory() . 'runtime')) {
                $this->logger->info('Adding runtime container files');
                $finder = Finder::create()
                    ->files()
                    ->in($this->package->getDirectory() . 'runtime/container');
                $targetPhar->addBundle($this->package->bundle($finder));
            }
        }
        // Add composer.lock
        $targetPhar->addFromString('composer.lock', json_encode($lock, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));


        // Add composer depenedencies.
        foreach ($this->getPackagesDependencies() as $package) {
            // Not add dev package .
            if ($this->noDev && empty($packagesList[$package->getName()])) {
                continue;
            }
            $this->logger->info('Adding dependency "' . $package->getName() . '" from "' . $this->getPathLocalToBase($package->getDirectory()) . '"');
            // support package symlink
            if (is_link(rtrim($package->getDirectory(), '/'))) {
                $bundle = $package->bundle();
                foreach ($bundle as $resource) {
                    foreach ($resource as $iterator) {
                        $targetPhar->addFile($iterator->getPathname());
                    }
                }
            } else {
                $targetPhar->addBundle($package->bundle());
            }
        }
        // Replace ConfigFactory ReadPaths method.
        $this->logger->info('Replace method "readPaths" in file "vendor/hyperf/config/src/ConfigFactory.php" and change "getRealPath" to "getPathname".');
        $this->replaceConfigFactoryReadPaths($targetPhar, $vendorPath);

        $this->logger->info('Adding main file "' . $main . '"');
        $stubContents = file_get_contents($main);
        $targetPhar->addFromString($main, strtr($stubContents, ['<?php' => $this->getMountLinkCode()]));

        $this->logger->info('Setting stub');
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
        $code = (new Ast())->parse($code, [new RewriteConfigVisitor()]);
        $targetPhar->addFromString($configPath, $code);
    }

    /**
     * Replace the method in the Config component to get the true path to the configuration file.
     */
    protected function replaceConfigFactoryReadPaths(TargetPhar $targetPhar, string $vendorPath)
    {
        $configPath = 'hyperf/config/src/ConfigFactory.php';
        $absPath = $vendorPath . $configPath;
        if (! file_exists($absPath)) {
            return;
        }
        $code = file_get_contents($absPath);
        $code = (new Ast())->parse($code, [new RewriteConfigFactoryVisitor()]);
        $targetPhar->addFromString('vendor/' . $configPath, $code);
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
        return round(filesize((string) $path) / 1024, 1) . ' KiB';
    }
}
