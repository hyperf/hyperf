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

namespace Hyperf\Nats\Driver;

use Hyperf\Nats\Contract\PublishInterface;
use Hyperf\Nats\Contract\RequestInterface;
use Hyperf\Nats\Contract\SubscribeInterface;

interface DriverInterface extends PublishInterface, RequestInterface, SubscribeInterface
{
}
