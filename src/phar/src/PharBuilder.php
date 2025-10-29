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
use Hyperf\Phar\Ast\Visitor\UnshiftCodeStringVisitor;
use InvalidArgumentException;
use JsonException;
use Phar;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use UnexpectedValueException;

class PharBuilder
{
    private Package $package;

    private null|string|TargetPhar $target = null;

    private array $mount = [];

    private ?string $version = null;

    private ?string $main = null;

    public function __construct(string $path, private LoggerInterface $logger)
    {
        $this->package = new Package($this->loadJson($path), dirname(realpath($path)));
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
     * @return $this
     */
    public function setTarget(string|TargetPhar $target): static
    {
        if (is_dir($target)) {
            $this->target = null;
            $target = rtrim($target, '/') . '/' . $this->getTarget();
        }
        $this->target = $target;
        return $this;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;
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
     */
    public function setMain(string $main): static
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

    public function setMount(array $mount = []): static
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
            // Adapt Composer 2.0
            if (isset($installed['packages'])) {
                $installedPackages = $installed['packages'];
            }
            // Package all of these dependent components into the packages
            foreach ($installedPackages as $package) {
                // support custom installation path
                $dir = 'composer/' . ($package['install-path'] ?? '../' . $package['name']) . '/';

                if (isset($package['target-dir'])) {
                    $dir .= trim($package['target-dir'], '/') . '/';
                }

                $dir = $vendorPath . $dir;
                $packages[] = new Package($package, $this->canonicalize($dir));
            }
        }
        return $packages;
    }

    /**
     * Gets the canonical path, like realpath.
     * @param mixed $address
     */
    public function canonicalize($address)
    {
        $address = explode('/', $address);
        $keys = array_keys($address, '..');

        foreach ($keys as $pos => $key) {
            array_splice($address, $key - ($pos * 2 + 1), 2);
        }

        $address = implode('/', $address);
        return str_replace('./', '', $address);
    }

    /**
     * Gets the relative path relative to the resource bundle.
     */
    public function getPathLocalToBase(string $path): ?string
    {
        $root = $this->package->getDirectory();
        if (! str_starts_with($path, $root)) {
            throw new UnexpectedValueException('Path "' . $path . '" is not within base project path "' . $root . '"');
        }
        $basePath = substr($path, strlen($root));
        return empty($basePath) ? null : $this->canonicalize($basePath);
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
    public function build(): void
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

        $targetPhar = new TargetPhar(new CustomPhar($tmp), $this);
        $this->logger->info('Adding main package "' . $this->package->getName() . '"');
        $finder = Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->exclude(rtrim($this->package->getVendorPath(), '/'))
            ->exclude('runtime') // Ignore runtime dir
            ->notPath('/^composer\.phar/')
            ->exclude($main)
            ->notPath($target); // Ignore the phar package that exists in the project itself

        foreach ($this->getMount() as $inside) {
            $finder = $finder->exclude($inside);
        }

        $finder = $finder->in($this->package->getDirectory());

        $targetPhar->addBundle($this->package->bundle($finder));

        // Force turning on ScanCacheable.
        $this->enableScanCacheable($targetPhar);

        // Load the Runtime folder separately
        if (is_dir($this->package->getDirectory() . 'runtime')) {
            $this->logger->info('Adding runtime container files');
            $finder = Finder::create()
                ->files()
                ->exclude($cacheFile = 'runtime/container/scan.cache')
                ->in($this->package->getDirectory() . 'runtime/container');
            $targetPhar->addBundle($this->package->bundle($finder));
            $cache = file_get_contents($cacheFile);
            $scanCache = unserialize($cache);
            $proxies = [];
            foreach ($scanCache[1] as $class => $path) {
                $proxies[$class] = $this->getPathLocalToBase($path);
            }
            $scanCache[1] = $proxies;
            $targetPhar->addFromString($cacheFile, serialize($scanCache));
        }

        // Add .env file.
        if (! in_array('.env', $this->getMount()) && is_file($this->package->getDirectory() . '.env')) {
            $this->logger->info('Adding .env file');
            $targetPhar->addFile($this->package->getDirectory() . '.env');
        }

        // Add vendor/bin files.
        if (is_dir($vendorPath . 'bin/')) {
            $this->logger->info('Adding vendor/bin files');
            $binIterator = new GlobIterator($vendorPath . 'bin/*');
            while ($binIterator->valid()) {
                $targetPhar->addFile($binIterator->getPathname());
                $binIterator->next();
            }
        }

        $this->logger->info('Adding composer base files');
        // Add composer autoload file.
        $targetPhar->addFile($vendorPath . 'autoload.php');

        // Add composer autoload files.
        $targetPhar->buildFromIterator(new GlobIterator($vendorPath . 'composer/*.*', FilesystemIterator::KEY_AS_FILENAME));

        // Add composer dependencies.
        foreach ($this->getPackagesDependencies() as $package) {
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
        $this->rewriteMainWithMountLinkCode($targetPhar, $main);

        $this->logger->info('Packaging all cache files into the PHAR archive.');
        $targetPhar->save();

        $this->logger->info('Setting stub');
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
    protected function enableScanCacheable(TargetPhar $targetPhar): void
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
    protected function replaceConfigFactoryReadPaths(TargetPhar $targetPhar, string $vendorPath): void
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

    protected function rewriteMainWithMountLinkCode(TargetPhar $targetPhar, string $mainPath): void
    {
        $code = file_get_contents($mainPath);
        $code = (new Ast())->parse($code, [new UnshiftCodeStringVisitor($this->getMountLinkCode())]);
        $targetPhar->addFromString($mainPath, $code);
    }

    /**
     * Load the configuration.
     */
    private function loadJson(string $path): array
    {
        try {
            $result = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(sprintf('Unable to parse given path %s', $path), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * Get file size.
     */
    private function getSize(PharBuilder|string $path): string
    {
        return round(filesize((string) $path) / 1024, 1) . ' KiB';
    }
}
