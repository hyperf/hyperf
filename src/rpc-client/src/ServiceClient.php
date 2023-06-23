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
namespace Hyperf\RpcClient;

use Hyperf\Collection\Arr;
use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\RpcClient\Exception\RequestException;
use Psr\Container\ContainerInterface;
use Throwable;

class ServiceClient extends AbstractServiceClient
{
    protected MethodDefinitionCollectorInterface $methodDefinitionCollector;

    protected string $serviceInterface;

    private NormalizerInterface $normalizer;

    public function __construct(ContainerInterface $container, string $serviceName, string $protocol = 'jsonrpc-http', array $options = [])
    {
        $this->serviceName = $serviceName;
        $this->protocol = $protocol;
        $this->setOptions($options);

        parent::__construct($container);

        $this->normalizer = $this->client->getNormalizer();
        $this->methodDefinitionCollector = $container->get(MethodDefinitionCollectorInterface::class);
    }

    protected function __request(string $method, array $params, ?string $id = null)
    {
        if ($this->idGenerator instanceof IdGeneratorInterface && ! $id) {
            $id = $this->idGenerator->generate();
        }
        $response = $this->client->send($this->__generateData($method, $params, $id));
        if (! is_array($response)) {
            throw new RequestException('Invalid response.');
        }

        $response = $this->checkRequestIdAndTryAgain($response, $id);
        if (array_key_exists('result', $response)) {
            $type = $this->methodDefinitionCollector->getReturnType($this->serviceInterface, $method);
            if ($type->allowsNull() && $response['result'] === null) {
                return null;
            }

            return $this->normalizer->denormalize($response['result'], $type->getName());
        }

        if ($code = $response['error']['code'] ?? null) {
            $error = $response['error'];
            // Denormalize exception.
            $class = Arr::get($error, 'data.class');
            $attributes = Arr::get($error, 'data.attributes', []);
            if (isset($class) && class_exists($class) && $e = $this->normalizer->denormalize($attributes, $class)) {
                if ($e instanceof Throwable) {
                    throw $e;
                }
            }

            // Throw RequestException when denormalize exception failed.
            throw new RequestException($error['message'] ?? '', $code, $error['data'] ?? []);
        }

        throw new RequestException('Invalid response.');
    }

    public function __call(string $method, array $params)
    {
        return $this->__request($method, $params);
    }

    protected function setOptions(array $options): void
    {
        $this->serviceInterface = $options['service_interface'] ?? $this->serviceName;

        if (isset($options['load_balancer'])) {
            $this->loadBalancer = $options['load_balancer'];
        }
    }
}
