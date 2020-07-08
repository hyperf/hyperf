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
namespace Hyperf\Nacos;

use Hyperf\Nacos\Model\ServiceModel;

class ThisService extends ServiceModel
{
    public function __construct()
    {
        $config = config('nacos.service');
        parent::__construct($config);
    }
}
