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

namespace Hyperf\DbConnection\Listeners;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\DbConnection\Connection;
use Hyperf\DbConnection\Context;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Event\AfterResponse;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
class AfterResponseListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->context = $container->get(Context::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            AfterResponse::class
        ];
    }

    public function process(object $event)
    {
        $connections = $this->context->connections();
        foreach ($connections as $conn) {
            if ($conn instanceof ConnectionInterface) {
                if ($conn instanceof Connection && $conn->isTransaction()) {
                    $conn->rollBack();
                    $this->logger->error('Maybe you\'ve forgotten to commit or rollback the MySQL transaction.');
                }
                $conn->release();
            }
        }
    }
}
