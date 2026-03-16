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

namespace Hyperf\Amqp\IO;

use Hyperf\Amqp\Exception\NotSupportedException;
use Hyperf\Amqp\Params;
use Hyperf\Engine\Constant;
use PhpAmqpLib\Wire\IO\AbstractIO;

class IOFactory implements IOFactoryInterface
{
    public function create(array $config, Params $params): AbstractIO
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 5672;
        $openSSL = $config['open_ssl'] ?? false;

        return match (Constant::ENGINE) {
            'Swoole' => new SwooleIO(
                $host,
                $port,
                $params->getConnectionTimeout(),
                $params->getReadWriteTimeout(),
                $openSSL
            ),
            'Swow' => new SwowIO(
                $host,
                $port,
                $params->getConnectionTimeout(),
                $params->getReadWriteTimeout()
            ),
            default => throw new NotSupportedException()
        };
    }
}
