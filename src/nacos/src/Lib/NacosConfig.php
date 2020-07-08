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
namespace Hyperf\Nacos\Lib;

use Hyperf\Nacos\Model\ConfigModel;

class NacosConfig extends AbstractNacos
{
    public function get(ConfigModel $configModel)
    {
        return $this->request('GET', '/nacos/v1/cs/configs', $configModel->toArray());
    }

    public function set(ConfigModel $configModel)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        return $this->request('POST', '/nacos/v1/cs/configs', $configModel->toArray(), $headers);
    }

    public function delete(ConfigModel $configModel)
    {
        return $this->request('DELETE', '/nacos/v1/cs/configs', $configModel->toArray());
    }
}
