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

namespace Hyperf\Kafka\Event;

use Hyperf\Kafka\AbstractConsumer;

class AfterConsume extends Consume
{
    public function __construct(AbstractConsumer $consumer, $data, protected ?string $result)
    {
        parent::__construct($consumer, $data);
    }
}
