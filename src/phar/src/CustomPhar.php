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
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class CustomPhar extends Phar
{
    private string $tempDir;

    public function __construct(string $filename, int $flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS, ?string $alias = null)
    {
        parent::__construct($filename, $flags, $alias);
        $this->tempDir = sys_get_temp_dir() . '/phar_cache_' . uniqid();
        $this->createDirectory($this->tempDir);
    }

    public function addFile(string $filename, ?string $localName = null): void
    {
        $localName = $localName ?? basename($filename);
        $relativePath = $this->tempDir . '/' . $localName;
        $this->createDirectory(dirname($relativePath));
        copy($filename, $relativePath);
    }

    public function addFromString(string $localName, string $contents): void
    {
        $relativePath = $this->tempDir . '/' . $localName;
        $this->createDirectory(dirname($relativePath));
        file_put_contents($relativePath, $contents);
    }

    public function buildFromDirectory(string $directory, ?string $pattern = null): array
    {
        $this->recursiveCopy($directory, $this->tempDir, $pattern);
        return [];
    }

    public function buildFromIterator($iterator, ?string $baseDirectory = null): array
    {
        foreach ($iterator as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            $relativePath = $baseDirectory ? str_replace(rtrim($baseDirectory, '/\\') . '/', '', $fileInfo->getRealPath()) : $fileInfo->getFilename();
            $this->addFile($fileInfo->getRealPath(), $relativePath);
        }
        return [];
    }

    /**
     * 批量保存文件.
     */
    public function save(): void
    {
        parent::buildFromDirectory($this->tempDir);
        $this->clearTempDir();
    }

    /**
     * 资源文件复制.
     */
    private function recursiveCopy(string $source, string $destination, ?string $pattern = null): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $destination . '/' . substr($item->getRealPath(), strlen($source) + 1);
            if ($item->isDir()) {
                $this->createDirectory($targetPath);
            } elseif (! $pattern || preg_match($pattern, $item->getFilename())) {
                copy($item->getRealPath(), $targetPath);
            }
        }
    }

    /**
     * 创建目录结构.
     */
    private function createDirectory(string $dir): void
    {
        if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new RuntimeException("Directory {$dir} was not created");
        }
    }

    /**
     * 清理临时目录.
     */
    private function clearTempDir(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tempDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            ($fileinfo->isDir() ? 'rmdir' : 'unlink')($fileinfo->getRealPath());
        }

        rmdir($this->tempDir);
    }
}
