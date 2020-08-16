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
namespace Hyperf\Nsq\Api;

use GuzzleHttp;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\CoroutineHandler;

class HttpClient implements HttpClientInterface
{
    /**
     * @var array
     */
    protected $options = [];

    public function __construct(ConfigInterface $config)
    {
        $options = $config->get('nsq.api.options', []);
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
}
