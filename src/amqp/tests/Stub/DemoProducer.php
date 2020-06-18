<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Amqp\Stub;

use Hyperf\Amqp\Message\ProducerMessage;

class DemoProducer extends ProducerMessage
{
    protected $exchange = 'hyperf';

    protected $routingKey = 'hyperf';

    public function __construct($data)
    {
        $this->payload = $data;
    }
}
