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
namespace Hyperf\ServiceGovernance;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ServiceGovernance\Register\ConsulAgent;
use Psr\Container\ContainerInterface;

class ConsulGovernance implements ServiceGovernanceInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function getNodes(): array
    {
        // TODO: Implement getNodes() method.
    }

    public function register(): void
    {
        // TODO: Implement register() method.
    }

    protected function client(): ConsulAgent
    {
        return $this->container->get(ConsulAgent::class);
    }
}
