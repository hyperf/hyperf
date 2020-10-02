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
namespace Hyperf\Nsq\Event;

use Hyperf\Nsq\AbstractConsumer;

class AfterConsume extends Consume
{
    /**
     * @var string
     */
    protected $result;

    public function __construct(AbstractConsumer $consumer, $data, string $result)
    {
        parent::__construct($consumer, $data);

        $this->result = $result;
    }
}
