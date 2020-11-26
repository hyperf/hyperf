<?php
declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Phar;


use FilesystemIterator;
use GlobIterator;
use Hyperf\Contract\StdoutLoggerInterface;
use InvalidArgumentException;
use Phar;
use Psr\Container\ContainerInterface;
use RuntimeException;
use UnexpectedValueException;

class HyperfPhar
{

    /**
     * @var Package
     */
    private $package;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var TargetPhar
     */
    private $target = null;

    /**
     * @var string
     */
    private $main = null;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @param ContainerInterface $container
     * @param string $path path to composer.json file
     */
    public function __construct(ContainerInterface $container,string $path)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->package = new Package($this->loadJson($path), dirname(realpath($path)));
    }


    /**
     * Gets the Phar package name
     * @return TargetPhar|string
     */
    public function getTarget()
    {
        if ($this->target === null) {
            $this->target = $this->package->getShortName() . '.phar';
        }
        return $this->target;
    }

    /**
     * Set the Phar package name
     * @param $target
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
     * Gets the default run script path
     * @return string
     */
    public function getMain()
    {
        if ($this->main === null) {
            foreach ($this->package->getBins() as $path) {
                if (!file_exists($this->package->getDirectory() . $path)) {
                    throw new UnexpectedValueException('Bin file "' . $path . '" does not exist');
                }
                $this->main = $path;
                break;
            }
            //兜底，使用hyperf默认启动文件
            if ($this->main == null){
                return "bin/hyperf.php";
            }
        }
        return $this->main;
    }

    /**
     * Set the default startup file
     * @param $main
     * @return $this
     */
    public function setMain($main)
    {
        $this->main = $main;
        return $this;
    }


    /**
     * Get package object
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Gets a list of all dependent packages
     * @return array
     */
    public function getPackagesDependencies()
    {
        $packages = array();

        $pathVendor = $this->package->getDirectory() . $this->package->getPathVendor();

        // 获取所有安装的依赖包
        if (is_file($pathVendor . 'composer/installed.json')) {

            $installed = $this->loadJson($pathVendor . 'composer/installed.json');
            $installedPackages = $installed;
            //支持composer 2.0 的配置结构改变
            if (isset($installed['packages'])){
                $installedPackages = $installed['packages'];
            }
            //把这些依赖的组件，全部打包成package
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
     *  Load the configuration
     * @param $path
     * @return mixed
     */
    private function loadJson($path)
    {
        $ret = json_decode(file_get_contents($path), true);
        if ($ret === null) {
            throw new InvalidArgumentException('Unable to parse given path "' . $path . '"', json_last_error());
        }
        return $ret;
    }

    /**
     * Get file size
     * @param $path
     * @return string
     */
    private function getSize($path)
    {
        return round(filesize($path) / 1024, 1) . ' KiB';
    }

    /**
     * Gets the relative path relative to the resource bundle
     * @param $path
     * @return false|string
     */
    public function getPathLocalToBase($path)
    {
        $root = $this->package->getDirectory();
        if (strpos($path, $root) !== 0) {
            throw new UnexpectedValueException('Path "' . $path . '" is not within base project path "' . $root . '"');
        }
        return substr($path, strlen($root));
    }

    /**
     * 输出log
     * @param $message
     */
    public function log($message)
    {
        $this->logger->info($message);
    }

    /**
     *  Compile the code into the Phar file
     */
    public function build()
    {
        $this->log('Creating phar <info>' . $this->getTarget() . '</info>');
        $time = microtime(true);

        //判断vendor目录是否存在
        $pathVendor = $this->package->getDirectory() . $this->package->getPathVendor();
        if (!is_dir($pathVendor)) {
            throw new RuntimeException('Directory "' . $pathVendor . '" not properly installed, did you run "composer install"?');
        }

        // 获取可以写入的phar包
        $target = $this->getTarget();
        do {
            $tmp = $target . '.' . mt_rand() . '.phar';
        } while (file_exists($tmp));

        $targetPhar = new TargetPhar(new Phar($tmp), $this);
        $this->log('  - Adding main package "' . $this->package->getName() . '"');
        //添加项目本身
        $targetPhar->addBundle($this->package->bundle());

        $this->log('  - Adding composer base files');
        // 显示的添加composer自动加载器
        $targetPhar->addFile($pathVendor . 'autoload.php');

        // 添加composer基本目录，没有子目录
        $targetPhar->buildFromIterator(new GlobIterator($pathVendor . 'composer/*.*', FilesystemIterator::KEY_AS_FILENAME));

        //添加composer的依赖资源
        foreach ($this->getPackagesDependencies() as $package) {
            $this->log('  - Adding dependency "' . $package->getName() . '" from "' . $this->getPathLocalToBase($package->getDirectory()) . '"');
            $targetPhar->addBundle($package->bundle());
        }

        $this->log('  - Setting main/stub');

        $main = $this->getMain();
        if ($main === null) {
            throw new RuntimeException("No main bin file defined! Resulting phar will NOT be executable");
        }
        //添加默认启动文件
        $targetPhar->setStub($targetPhar->createDefaultStub($main));
        $this->log('  - Setting default stub <info>'.$main .'</info>.');

        // 停止内存缓存，并写入数据到phar文件中，如果发生异常则会抛出
        $targetPhar->stopBuffering();

        if (file_exists($target)) {
            $this->log('  - Overwriting existing file <info>' . $target . '</info> (' . $this->getSize($target) . ')');
        }

        if (rename($tmp, $target) === false) {
            throw new UnexpectedValueException('Unable to rename temporary phar archive to "'.$target.'"');
        }

        $time = max(microtime(true) - $time, 0);

        $this->log('');
        $this->log('    <info>OK</info> - Creating <info>' . $this->getTarget() .'</info> (' . $this->getSize($this->getTarget()) . ') completed after ' . round($time, 1) . 's');
    }
}