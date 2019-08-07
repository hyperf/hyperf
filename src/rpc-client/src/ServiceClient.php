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

use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\RpcClient\Exception\RequestException;
use Psr\Container\ContainerInterface;

class ServiceClient extends AbstractServiceClient
{
    /**
     * @var MethodDefinitionCollectorInterface
     */
    protected $methodDefinitionCollector;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(ContainerInterface $container, string $serviceName, string $protocol = 'jsonrpc-http', array $options = [])
    {
        $this->serviceName = $serviceName;
        $this->protocol = $protocol;
        $this->setOptions($options);
        parent::__construct($container);
        $this->normalizer = $container->get(NormalizerInterface::class);
        $this->methodDefinitionCollector = $container->get(MethodDefinitionCollectorInterface::class);
    }

    protected function __request(string $method, array $params, ?string $id = null)
    {
        if ($this->idGenerator instanceof IdGeneratorInterface && ! $id) {
            $id = $this->idGenerator->generate();
        }
        $response = $this->client->send($this->__generateData($method, $params, $id));
        if (is_array($response)) {
            if (isset($response['result'])) {
                $type = $this->methodDefinitionCollector->getReturnType($this->serviceName, $method);
                return $this->normalizer->denormalize($response['result'], $type->getName());
            }
            if (isset($response['error'])) {
                $error = $response['error'];
                if (isset($error['code'])) {
                    if (isset($error['data']['class'], $error['data']['attributes'])) {
                        $data = $error['data'];
                        $e = $this->normalizer->denormalize($data['attributes'] ?? [], $data['class']);
                        if ($e instanceof \Exception) {
                            throw $e;
                        }
                    }
                    throw new RequestException($error['message'] ?? '', $error['code']);
                }
            }
        }
        throw new RequestException('Invalid response.');
    }

    public function __call(string $method, array $params)
    {
        return $this->__request($method, $params);
    }

    protected function setOptions(array $options): void
    {
        if (isset($options['load_balancer'])) {
            $this->loadBalancer = $options['load_balancer'];
        }
    }
}
