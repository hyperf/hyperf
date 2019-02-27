<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Elasticsearch\Connections;

use Psr\Log\LoggerInterface;
use Elasticsearch\Serializers\SerializerInterface;
use Elasticsearch\Connections\ConnectionFactoryInterface;

/**
 * Class AbstractConnection.
 *
 * @category Elasticsearch
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache2
 * @see     http://elastic.co
 */
class ConnectionFactory implements ConnectionFactoryInterface
{
    /** @var array */
    private $connectionParams;

    /** @var SerializerInterface */
    private $serializer;

    /** @var LoggerInterface */
    private $logger;

    /** @var LoggerInterface */
    private $tracer;

    /** @var callable */
    private $handler;

    /**
     * Constructor.
     *
     * @param callable $handler
     * @param array $connectionParams
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param LoggerInterface $tracer
     */
    public function __construct(callable $handler, array $connectionParams, SerializerInterface $serializer, LoggerInterface $logger, LoggerInterface $tracer)
    {
        $this->handler = $handler;
        $this->connectionParams = $connectionParams;
        $this->logger = $logger;
        $this->tracer = $tracer;
        $this->serializer = $serializer;
    }

    /**
     * @param $hostDetails
     *
     * @return ConnectionInterface
     */
    public function create($hostDetails)
    {
        return new Connection(
            $this->handler,
            $hostDetails,
            $this->connectionParams,
            $this->serializer,
            $this->logger,
            $this->tracer
        );
    }
}
