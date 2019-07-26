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

namespace Hyperf\Etcd;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Etcd\Exception\ClientNotFindException;
use Hyperf\Etcd\V3\KV;
use Hyperf\Utils\Arr;

/**
 * @property KVInterface $kv
 */
class ClientFactory
{
    const CLIENT_MAP = [
        'v3' => [
            'kv' => KV::class,
        ],
    ];

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $clients = [];

    public function __construct(ConfigInterface $config)
    {
        $this->baseUri = $config->get('etcd.uri', 'http://127.0.0.1:2379');
        $this->version = $config->get('etcd.version', 'v3beta');
        $this->options = $config->get('etcd.options', []);
    }

    public function __get($name)
    {
        if (isset($this->clients[$name]) && $this->clients[$name] instanceof Client) {
            return $this->clients[$name];
        }

        $version = substr($this->version, 0, 2);
        $className = Arr::get(static::CLIENT_MAP, $version . '.' . $name);
        if (! is_string($className) || ! class_exists($className)) {
            throw new ClientNotFindException("{$className} is not find.");
        }

        return $this->clients[$name] = make($className, [
            'baseUri' => sprintf('%s/%s/', $this->baseUri, $this->version),
            'options' => $this->options,
        ]);
    }
}
