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

use Phar;
use Symfony\Component\Finder\Finder;
use Traversable;

class TargetPhar
{
    /**
     * @var Phar
     */
    private $phar;

    /**
     * @var  HyperfPhar
     */
    private $hyperfPhar;

    public function __construct(Phar $phar, HyperfPhar $hyperfPhar)
    {
        $phar->startBuffering();
        $this->phar = $phar;
        $this->hyperfPhar = $hyperfPhar;
    }

    /**
     * 开始写入phar包
     */
    public function stopBuffering()
    {
        $this->phar->stopBuffering();
    }

    /**
     * 添加资源包到phar包中
     *
     * @param  Bundle  $bundle
     */
    public function addBundle(Bundle $bundle)
    {
        /** @var Finder $resource */
        foreach ($bundle as $resource) {
            if (is_string($resource)) {
                $this->addFile($resource);
            } else {
                $this->buildFromIterator($resource);
            }
        }
    }

    /**
     * 添加文件到Phar包中
     *
     * @param string $file The file name.
     */
    public function addFile(string $file)
    {
        $this->phar->addFile($file, $this->hyperfPhar->getPathLocalToBase($file));
    }

    /**
     * 添加文件夹资源到Phar包中
     * @param Traversable $iterator
     */
    public function buildFromIterator(Traversable $iterator)
    {
        $this->phar->buildFromIterator($iterator, $this->hyperfPhar->getPackage()->getDirectory());
    }


    /**
     * @param string|null $indexFile
     * @param string|null $webIndexFile
     * @return string
     */
    public function createDefaultStub(string $indexFile = null, string $webIndexFile = null)
    {
        return $this->phar->createDefaultStub($indexFile, $webIndexFile);
    }

    /**
     * 设置默认启动文件
     * @param string $stub
     */
    public function setStub(string $stub)
    {
        $this->phar->setStub($stub);
    }

    /**
     * 添加字符串到phar包中
     * @param $local
     * @param $contents
     */
    public function addFromString($local, $contents)
    {
        $this->phar->addFromString($local, $contents);
    }
}
