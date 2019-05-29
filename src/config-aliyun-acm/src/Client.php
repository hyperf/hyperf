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
use Psr\Container\ContainerInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;

class Client implements ClientInterface
{
    /**
     * @var Closure
     */
    private $httpClientFactory;

    /**
     * @var null|ConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $servers;

    /**
     * @var array
     */
    public $fetchConfig;

    public function __construct(ContainerInterface $container) {
        $this->httpClientFactory = $container->get(GuzzleClientFactory::class)->create();
        $this->config = $container->get(ConfigInterface::class);
    }

    public function pull(): void
    {
        $client = $this->httpClientFactory;
        if (! $client instanceof \GuzzleHttp\Client) {
            throw new \RuntimeException('aliyun acm: Invalid http client.');
        }

        // config
        $addressServer = $this->config->get('aliyun_acm.addressServer', 'acm.aliyun.com');
        $namespace = $this->config->get('aliyun_acm.namespace', 'namespace-id');
        $dataId = $this->config->get('aliyun_acm.dataId', 'data-id');
        $group = $this->config->get('aliyun_acm.group', 'group');
        $ak = $this->config->get('aliyun_acm.ak', 'ak');
        $sk = $this->config->get('aliyun_acm.sk', 'sk');

        // sign
        $ts = round(microtime(true) * 1000);
        $sign = base64_encode(hash_hmac('sha1', "$namespace+$group+$ts", $sk, true));

        if (!$this->servers) {
            // server list
            $r = $client->get("http://{$addressServer}:8080/diamond-server/diamond");
            if ($r->getStatusCode() !== 200) {
                throw new \RuntimeException('aliyun acm: get server list fail.');
            }
            $this->servers = array_filter(explode("\n", $r->getBody()->getContents()));
        }
        $server = $this->servers[array_rand($this->servers)];

        // get config
        $r = $client->get("http://{$server}:8080/diamond-server/config.co", [
            'headers' => [
                'Spas-AccessKey' => $ak,
                'timeStamp' => $ts,
                'Spas-Signature' => $sign,
            ],
            'query' => [
                'tenant' => $namespace,
                'dataId' => $dataId,
                'group' => $group
            ],
        ]);
        if ($r->getStatusCode() !== 200) {
            throw new \RuntimeException('aliyun acm: get config fail.');
        }
        $this->fetchConfig = json_decode($r->getBody()->getContents(), true);
    }
}
