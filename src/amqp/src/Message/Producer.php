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

namespace Hyperf\Amqp\Message;

use Hyperf\Amqp\Constants;
use Hyperf\Amqp\Packer\Packer;
use Hyperf\Framework\ApplicationContext;

abstract class Producer extends Message implements ProducerInterface
{
    protected $payload;

    protected $properties = [
        'content_type' => 'text/plain',
        'delivery_mode' => Constants::DELIVERY_MODE_PERSISTENT
    ];

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function payload(): string
    {
        return $this->serialize();
    }

    public function serialize(): string
    {
        $application = ApplicationContext::getContainer();
        $packer = $application->get(Packer::class);

        return $packer->pack($this->payload);
    }
}
