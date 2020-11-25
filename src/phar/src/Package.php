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


use Symfony\Component\Finder\Finder;

class Package
{

    private $package;
    private $directory;


    /**
     * Package constructor.
     * @param array $package
     * @param $directory
     */
    public function __construct(array $package, $directory)
    {
        $this->package = $package;
        $this->directory = rtrim($directory, '/') . '/';
    }

    /**
     * 获取包全名
     * @return mixed|null
     */
    public function getName()
    {
        return isset($this->package['name']) ? $this->package['name'] : null;
    }

    /**
     * 获取短包名
     * 如果没有获取到，则使用路径名作为包名
     * @return string
     */
    public function getShortName()
    {
        $name = $this->getName();
        if ($name === null) {
            $name = realpath($this->directory);
            if ($name === false) {
                $name = $this->directory;
            }
        }
        return basename($name);
    }

    /**
     * 获取vendor目录的相对地址，支持composer.json中的自定义地址
     * @return string
     */
    public function getPathVendor()
    {
        $vendor = 'vendor';
        if (isset($this->package['config']['vendor-dir'])) {
            $vendor = $this->package['config']['vendor-dir'];
        }
        return $vendor . '/';
    }

    /**
     * 获取包目录
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * 获取资源包
     * @return Bundle
     */
    public function bundle()
    {
        $bundle = new Bundle();

        if (empty($this->package['autoload']) && !is_dir($this->directory . $this->getPathVendor())) {
            return $bundle;
        }
        $iterator = Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->exclude(rtrim($this->getPathVendor(), '/'))
            ->notPath('/^composer\.phar/')
            ->in($this->getDirectory());

        return $bundle->addDir($iterator);
    }

    /**
     * 获取可执行文件路径，phar包打包后运行的目录地址
     * @return array|mixed
     */
    public function getBins()
    {
        return isset($this->package['bin']) ? $this->package['bin'] : array();
    }

}