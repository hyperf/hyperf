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

namespace Hyperf\ConfigAliyunAcm;

use Hyperf\ConfigCenter\AbstractDriver;
use Psr\Container\ContainerInterface;

class AliyunAcmDriver extends AbstractDriver
{
    protected string $driverName = 'aliyun_acm';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
    }
}
