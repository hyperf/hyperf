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
namespace Hyperf\ConfigAliyunAcm;

use GuzzleHttp;
use Hyperf\Codec\Json;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class Client implements ClientInterface
{
    private GuzzleHttp\Client $client;

    private ConfigInterface $config;

    private LoggerInterface $logger;

    private array $servers = [];

    /**
     * @var array[]
     */
    private $cachedSecurityCredentials = [];

    public function __construct(ContainerInterface $container)
    {
        $clientFactory = $container->get(GuzzleClientFactory::class);
        $this->client = $clientFactory->create();
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function pull(): array
    {
        // ACM config
        $endpoint = $this->config->get('config_center.drivers.aliyun_acm.endpoint', 'acm.aliyun.com');
        $namespace = $this->config->get('config_center.drivers.aliyun_acm.namespace', '');
        $dataId = $this->config->get('config_center.drivers.aliyun_acm.data_id', '');
        $group = $this->config->get('config_center.drivers.aliyun_acm.group', 'DEFAULT_GROUP');
        $accessKey = $this->config->get('config_center.drivers.aliyun_acm.access_key', '');
        $secretKey = $this->config->get('config_center.drivers.aliyun_acm.secret_key', '');
        $ecsRamRole = (string) $this->config->get('config_center.drivers.aliyun_acm.ecs_ram_role', '');
        $securityToken = [];
        if (empty($accessKey) && ! empty($ecsRamRole)) {
            $securityCredentials = $this->getSecurityCredentialsWithEcsRamRole($ecsRamRole);
            if (! empty($securityCredentials)) {
                $accessKey = $securityCredentials['AccessKeyId'];
                $secretKey = $securityCredentials['AccessKeySecret'];
                $securityToken = [
                    'Spas-SecurityToken' => $securityCredentials['SecurityToken'],
                ];
            }
        }

        // Sign
        $timestamp = round(microtime(true) * 1000);
        $sign = base64_encode(hash_hmac('sha1', "{$namespace}+{$group}+{$timestamp}", $secretKey, true));

        try {
            if (! $this->servers) {
                // server list
                $response = $this->client->get("http://{$endpoint}:8080/diamond-server/diamond");
                if ($response->getStatusCode() !== 200) {
                    throw new RuntimeException('Get server list failed from Aliyun ACM.');
                }
                $this->servers = array_filter(explode("\n", (string) $response->getBody()));
            }
            $server = $this->servers[array_rand($this->servers)];

            // Get config
            $response = $this->client->get("http://{$server}:8080/diamond-server/config.co", [
                'headers' => array_merge([
                    'Spas-AccessKey' => $accessKey,
                    'timeStamp' => $timestamp,
                    'Spas-Signature' => $sign,
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
                ], $securityToken),
                'query' => [
                    'tenant' => $namespace,
                    'dataId' => $dataId,
                    'group' => $group,
                ],
            ]);
            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('Get config failed from Aliyun ACM.');
            }
            $content = (string) $response->getBody();
            if (! $content) {
                return [];
            }
            return Json::decode($content);
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('%s[line:%d] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            return [];
        }
    }

    /**
     * Get ECS RAM authorization.
     * @see https://help.aliyun.com/document_detail/72013.html
     * @see https://help.aliyun.com/document_detail/54579.html?#title-9w8-ufj-kz6
     */
    private function getSecurityCredentialsWithEcsRamRole(string $ecsRamRole): ?array
    {
        $securityCredentials = $this->cachedSecurityCredentials[$ecsRamRole] ?? null;

        /* @phpstan-ignore-next-line */
        if (! empty($securityCredentials) && time() > strtotime($securityCredentials['Expiration']) - 60) {
            $securityCredentials = null;
        }
        if (empty($securityCredentials)) {
            $response = $this->client->get('http://100.100.100.200/latest/meta-data/ram/security-credentials/' . $ecsRamRole);
            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('Get config failed from Aliyun ACM.');
            }
            $securityCredentials = Json::decode((string) $response->getBody());
            if (! empty($securityCredentials)) {
                $this->cachedSecurityCredentials[$ecsRamRole] = $securityCredentials;
            }
        }
        return $securityCredentials;
    }
}
