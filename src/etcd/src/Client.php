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
namespace Hyperf\Etcd;

use GuzzleHttp\HandlerStack;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Utils\Coroutine;

abstract class Client
{
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
     * @var HandlerStack[]
     */
    protected $stacks = [];

    /**
     * @var HandlerStackFactory
     */
    protected $factory;

    public function __construct(ConfigInterface $config, HandlerStackFactory $factory)
    {
        $uri = $config->get('etcd.uri', 'http://127.0.0.1:2379');
        $version = $config->get('etcd.version', 'v3beta');

        $this->options = $config->get('etcd.options', []);
        $this->baseUri = sprintf('%s/%s/', $uri, $version);
        $this->factory = $factory;
    }

    protected function getDefaultHandler()
    {
        $id = (int) Coroutine::inCoroutine();
        if (isset($this->stacks[$id]) && $this->stacks[$id] instanceof HandlerStack) {
            return $this->stacks[$id];
        }

        return $this->stacks[$id] = $this->factory->create();
    }
}
