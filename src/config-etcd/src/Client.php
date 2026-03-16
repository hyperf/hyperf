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

namespace Hyperf\ConfigEtcd;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Etcd\KVInterface;

class Client implements ClientInterface
{
    public function __construct(protected KVInterface $client, protected ConfigInterface $config)
    {
    }

    public function pull(): array
    {
        $namespaces = $this->config->get('config_center.drivers.etcd.namespaces');
        $kvs = [];
        foreach ($namespaces as $namespace) {
            $res = $this->client->fetchByPrefix($namespace);
            if (isset($res['kvs'])) {
                foreach ($res['kvs'] as $kv) {
                    $kvs[$kv['key']] = $kv;
                }
            }
        }

        return $kvs;
    }
}
