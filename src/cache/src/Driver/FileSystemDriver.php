<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Cache\Driver;

use Hyperf\Cache\Exception\CacheException;
use Hyperf\Cache\Exception\InvalidArgumentException;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

/**
 * Class FileSystemDriver.
 */
class FileSystemDriver extends Driver implements KeyCollectorInterface
{
    /**
     * @var string
     */
    protected $store_path = BASE_PATH . '/runtime/caches';

    /**
     * @var string
     */
    protected $ds = DIRECTORY_SEPARATOR;

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);
        $store_path = $container->get(ConfigInterface::class)->get('cache.default.store');
        if (! empty($store_path)) {
            $this->store_path = rtrim($store_path, $this->ds);
        }
        if (! file_exists($this->store_path)) {
            $results = mkdir($this->store_path, 0777, true);
            if (! $results) {
                throw new CacheException('Has no permission to create cache directory!');
            }
        }
    }

    public function getCacheKey(string $key)
    {
        $cachePrefix = $this->store_path . $this->ds . $this->prefix . $key;

        return [$cachePrefix . '.tmp', $cachePrefix . '.ttl'];
    }

    public function get($key, $default = null)
    {
        [$contentFile, $ttlFile] = $this->getCacheKey($key);
        if (! file_exists($contentFile)) {
            return $default;
        }
        if (file_exists($ttlFile)) {
            if (time() < (int) file_get_contents($ttlFile)) {
                return $default;
            }
        }

        $cacheContent = file_get_contents($contentFile);

        return $this->packer->unpack($cacheContent);
    }

    public function fetch(string $key, $default = null): array
    {
        [$contentFile, $ttlFile] = $this->getCacheKey($key);
        if (! file_exists($contentFile)) {
            return [false, $default];
        }
        if (file_exists($ttlFile)) {
            if (time() < (int) file_get_contents($ttlFile)) {
                return [false, $default];
            }
        }

        $cacheContent = file_get_contents($contentFile);

        return [true, $this->packer->unpack($cacheContent)];
    }

    public function set($key, $value, $ttl = null)
    {
        $res = $this->packer->pack($value);
        [$contentFile, $ttlFile] = $this->getCacheKey($key);
        $result = file_put_contents($contentFile, $res, FILE_BINARY);
        if (! $result) {
            return boolval($result);
        }
        if ($ttl > 0) {
            file_put_contents($ttlFile, time() + (int) $ttl, FILE_BINARY);
        }

        return $result;
    }

    public function delete($key)
    {
        [$contentFile, $ttlFile] = $this->getCacheKey($key);
        if (file_exists($contentFile)) {
            if (! is_writable($contentFile)) {
                return false;
            }
            unlink($contentFile);
        }
        if (file_exists($ttlFile)) {
            unlink($ttlFile);
        }

        return true;
    }

    public function clear()
    {
        return $this->clearPrefix('');
    }

    public function getMultiple($keys, $default = null)
    {
        if (! is_array($keys)) {
            throw new InvalidArgumentException('The keys is invalid!');
        }

        $result = [];
        foreach ($keys as $i => $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        if (! is_array($values)) {
            throw new InvalidArgumentException('The values is invalid!');
        }

        $cacheKeys = [];
        foreach ($values as $key => $value) {
            $cacheKeys[$this->prefix . $key] = $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple($keys)
    {
        if (! is_array($keys)) {
            throw new InvalidArgumentException('The keys is invalid!');
        }

        foreach ($keys as $index => $key) {
            return $this->delete($key);
        }

        return true;
    }

    public function has($key)
    {
        [$contentFile, $ttlFile] = $this->getCacheKey($key);

        return file_exists($contentFile);
    }

    public function clearPrefix(string $prefix): bool
    {
        $files = glob($this->store_path . $prefix . '*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }
            unlink($file);
        }

        return true;
    }

    public function addKey(string $collector, string $key): bool
    {
        //  do nothing
        return true;
    }

    public function keys(string $collector): array
    {
        $globPattern = $this->store_path . $this->ds . $this->prefix . $collector;
        $files = glob($globPattern . '*');

        $results = [];
        foreach ($files as $index => $file) {
            $results[] = str_replace($this->store_path . $this->ds, '', $files);
        }

        return $results;
    }

    public function delKey(string $collector, ...$key): bool
    {
        //  do nothing
        return true;
    }
}
