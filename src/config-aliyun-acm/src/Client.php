<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ConfigAliyunAcm;

use Closure;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Client implements ClientInterface
{
    /**
     * @var array
     */
    public $fetchConfig;

    /**
     * @var Closure
     */
    private $client;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $servers;

    public function __construct(ContainerInterface $container)
    {
        $this->client = $container->get(GuzzleClientFactory::class)->create();
        $this->config = $container->get(ConfigInterface::class);
    }

    public function pull(): array
    {
        $client = $this->client;
        if (! $client instanceof \GuzzleHttp\Client) {
            throw new RuntimeException('aliyun acm: Invalid http client.');
        }

        // config
        $addressServer = $this->config->get('aliyun_acm.endpoint', 'acm.aliyun.com');
        $namespace = $this->config->get('aliyun_acm.namespace', '');
        $dataId = $this->config->get('aliyun_acm.data_id', '');
        $group = $this->config->get('aliyun_acm.group', 'DEFAULT_GROUP');
        $ak = $this->config->get('aliyun_acm.access_key', '');
        $sk = $this->config->get('aliyun_acm.secret_key', '');

        // sign
        $timestamp = round(microtime(true) * 1000);
        $sign = base64_encode(hash_hmac('sha1', "{$namespace}+{$group}+{$timestamp}", $sk, true));

        if (! $this->servers) {
            // server list
            $response = $client->get("http://{$addressServer}:8080/diamond-server/diamond");
            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('aliyun acm: get server list fail.');
            }
            $this->servers = array_filter(explode("\n", $response->getBody()->getContents()));
        }
        $server = $this->servers[array_rand($this->servers)];

        // get config
        $response = $client->get("http://{$server}:8080/diamond-server/config.co", [
            'headers' => [
                'Spas-AccessKey' => $ak,
                'timeStamp' => $timestamp,
                'Spas-Signature' => $sign,
            ],
            'query' => [
                'tenant' => $namespace,
                'dataId' => $dataId,
                'group' => $group,
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('aliyun acm: get config fail.');
        }
        return json_decode($response->getBody()->getContents(), true);
    }
}
