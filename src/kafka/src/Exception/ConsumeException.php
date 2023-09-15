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
namespace Hyperf\Kafka\Exception;

use longlang\phpkafka\Consumer\ConsumeMessage;
use Throwable;

class ConsumeException extends KafkaException
{
    public function __construct(
        protected ConsumeMessage $consumeMessage,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct('Fail to consume message', $code, $previous);
    }

    public function getConsumeMessage(): ConsumeMessage
    {
        return $this->consumeMessage;
    }
}
