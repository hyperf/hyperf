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

namespace Hyperf\RpcClient;

use Hyperf\Utils\Traits\Container;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use Psr\Container\ContainerInterface;

class ServiceAdapter implements AdapterInterface
{
    use Container;

    /**
     * @var ContainerInterface
     */
    private $di;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var array
     */
    private $options;

    public function __construct(ContainerInterface $container, string $protocol = 'jsonrpc-http', array $options = [])
    {
        $this->di = $container;
        $this->protocol = $protocol;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function call(string $wrappedClass, string $method, array $params = [])
    {
        $key = $this->protocol . ':' . $wrappedClass;
        if (! self::has($key)) {
            self::set($key, new ServiceClient($this->di, $wrappedClass, $this->protocol, $this->options));
        }
        return self::get($key)->call($method, $params);
    }
}
