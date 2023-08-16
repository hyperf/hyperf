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
namespace Hyperf\Cache\Driver;

use Hyperf\Cache\Collector\FileStorage;
use Hyperf\Cache\Exception\CacheException;
use Hyperf\Cache\Exception\InvalidArgumentException;
use Hyperf\Support\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;

class FileSystemDriver extends Driver
{
    /**
     * @var string
     */
    protected $storePath = BASE_PATH . '/runtime/caches';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);
        if (! is_dir($this->storePath)) {
            $results = mkdir($this->storePath, 0777, true);
            if (! $results) {
                throw new CacheException('Has no permission to create cache directory!');
            }
        }
        $this->filesystem = $container->get(Filesystem::class);
    }

    public function getCacheKey(string $key)
    {
        return $this->getPrefix() . $key . '.cache';
    }

    public function get($key, $default = null): mixed
    {
        $file = $this->getCacheKey($key);
        if (! file_exists($file)) {
            return $default;
        }

        /** @var FileStorage $obj */
        $obj = $this->packer->unpack($this->filesystem->get($file));
        if ($obj->isExpired()) {
            return $default;
        }

        return $obj->getData();
    }

    public function fetch(string $key, $default = null): array
    {
        $file = $this->getCacheKey($key);
        if (! file_exists($file)) {
            return [false, $default];
        }

        /** @var FileStorage $obj */
        $obj = $this->packer->unpack($this->filesystem->get($file));
        if ($obj->isExpired()) {
            return [false, $default];
        }

        return [true, $obj->getData()];
    }

    public function set($key, $value, $ttl = null): bool
    {
        $seconds = $this->secondsUntil($ttl);
        $file = $this->getCacheKey($key);
        $content = $this->packer->pack(new FileStorage($value, $seconds));

        $result = $this->filesystem->put($file, $content);

        return (bool) $result;
    }

    public function delete($key): bool
    {
        $file = $this->getCacheKey($key);
        if (file_exists($file)) {
            if (! is_writable($file)) {
                return false;
            }
            unlink($file);
        }

        return true;
    }

    public function clear(): bool
    {
        return $this->clearPrefix('');
    }

    public function getMultiple($keys, $default = null): iterable
    {
        if (! is_array($keys)) {
            throw new InvalidArgumentException('The keys is invalid!');
        }

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        if (! is_array($values)) {
            throw new InvalidArgumentException('The values is invalid!');
        }
        $seconds = $this->secondsUntil($ttl);
        foreach ($values as $key => $value) {
            $this->set($key, $value, $seconds);
        }

        return true;
    }

    public function deleteMultiple($keys): bool
    {
        if (! is_array($keys)) {
            throw new InvalidArgumentException('The keys is invalid!');
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has($key): bool
    {
        $file = $this->getCacheKey($key);

        return file_exists($file);
    }

    public function clearPrefix(string $prefix): bool
    {
        $files = glob($this->getPrefix() . $prefix . '*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }
            unlink($file);
        }

        return true;
    }

    protected function getPrefix()
    {
        return $this->storePath . DIRECTORY_SEPARATOR . $this->prefix;
    }
}
