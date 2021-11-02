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

use Hyperf\Amqp\Params;
use PhpAmqpLib\Wire\IO\AbstractIO;

class SwooleIOFactory
{
    public function __invoke(array $config, Params $params): AbstractIO
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 5672;

        return new SwooleIO(
            $host,
            $port,
            $params->getConnectionTimeout()
        );
    }
}
