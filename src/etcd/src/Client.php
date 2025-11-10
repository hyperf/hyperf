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
use Hyperf\Coroutine\Coroutine;
use Hyperf\Guzzle\HandlerStackFactory;

abstract class Client
{
    protected string $baseUri;

    /**
     * @var HandlerStack[]
     */
    protected array $stacks = [];

    public function __construct(
        protected string $uri,
        protected string $version,
        protected array $auth,
        protected array $options,
        protected HandlerStackFactory $factory
    ) {
        $this->baseUri = sprintf('%s/%s/', $uri, $version);
    }

    protected function getDefaultHandler(): HandlerStack
    {
        $id = (int) Coroutine::inCoroutine();
        if (isset($this->stacks[$id]) && $this->stacks[$id] instanceof HandlerStack) {
            return $this->stacks[$id];
        }

        if ($this->auth['name'] ?? '' && $this->auth['password'] ?? '') {
            return $this->stacks[$id] = $this->factory->create([
                'config' => [
                    'uri' => $this->uri,
                    'version' => $this->version,
                    'auth' => $this->auth,
                    'options' => $this->options,
                ],
            ]);
        }

        return $this->stacks[$id] = $this->factory->create();
    }
}
