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
namespace Hyperf\Nacos\Service;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Model\ServiceModel;

class Service extends ServiceModel
{
    public function __construct(ConfigInterface $config)
    {
        $serviceConfig = $config->get('nacos.service', []);
        parent::__construct($serviceConfig);
    }
}
