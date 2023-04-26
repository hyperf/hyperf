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
namespace Hyperf\Etcd\V3;

use GuzzleHttp;
use Hyperf\Etcd\Client;
use Hyperf\Etcd\KVInterface;

use function Hyperf\Support\make;

class KV extends Client implements KVInterface
{
    public function put($key, $value, array $options = [])
    {
        return $this->client()->put($key, $value, $options);
    }

    public function get($key, array $options = [])
    {
        return $this->client()->get($key, $options);
    }

    public function fetchByPrefix($prefix)
    {
        $prefix = trim($prefix);
        if (! $prefix) {
            return [];
        }
        $lastIndex = strlen($prefix) - 1;
        $lastChar = $prefix[$lastIndex];
        $nextAsciiCode = ord($lastChar) + 1;
        $rangeEnd = $prefix;
        $rangeEnd[$lastIndex] = chr($nextAsciiCode);

        return $this->client()->get($prefix, ['range_end' => $rangeEnd]);
    }

    public function delete($key, array $options = [])
    {
        return $this->client()->del($key, $options);
    }

    protected function client(): EtcdClient
    {
        $options = array_replace([
            'base_uri' => $this->baseUri,
            'handler' => $this->getDefaultHandler(),
        ], $this->options);

        $client = make(GuzzleHttp\Client::class, [
            'config' => $options,
        ]);

        return make(EtcdClient::class, [
            'client' => $client,
        ]);
    }
}
