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

namespace Hyperf\Nsq\Nsqd;

use GuzzleHttp;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\CoroutineHandler;

class Client implements ClientInterface
{
    protected array $options = [];

    public function __construct(ConfigInterface $config, string $pool = 'default')
    {
        $nsq = $config->get('nsq.' . $pool, []);
        $options = $nsq['nsqd']['options'] ?? [];
        if (! isset($options['base_uri'])) {
            $options['base_uri'] = sprintf('http://%s:%s', $nsq['host'] ?? '127.0.0.1', $nsq['nsqd']['port'] ?? 4151);
        }
        if (! isset($options['handler']) && class_exists(CoroutineHandler::class)) {
            $options['handler'] = new CoroutineHandler();
        }

        $this->options = $options;
    }

    public function request($method, $uri, array $options = [])
    {
        $client = new GuzzleHttp\Client($this->options);

        return $client->request($method, $uri, $options);
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
