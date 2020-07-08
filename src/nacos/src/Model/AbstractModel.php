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
namespace Hyperf\Nacos\Model;

abstract class AbstractModel
{
    public $required_field = [];

    public function __construct($config = [])
    {
        foreach ($config as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }

    public function __toString()
    {
        return http_build_query($this->getParams());
    }

    public function getParams()
    {
        $params = array_filter(get_object_vars($this), function ($item) {
            return $item !== null;
        });
        unset($params['required_field']);
        $intersect = array_intersect(array_keys($params), $this->required_field);
        sort($this->required_field);
        sort($intersect);
        if ($intersect !== $this->required_field) {
            throw new \Exception('缺少关键信息' . implode(',', $this->required_field));
        }

        return $params;
    }

    public function toJson()
    {
        return json_encode($this->getParams());
    }

    public function toArray()
    {
        return $this->getParams();
    }
}
