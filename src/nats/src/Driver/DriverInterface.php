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

namespace Hyperf\Nats\Driver;

use Hyperf\Nats\PublishInterface;
use Hyperf\Nats\RequestInterface;
use Hyperf\Nats\SubscribeInterface;

interface DriverInterface extends PublishInterface, RequestInterface, SubscribeInterface
{
}
