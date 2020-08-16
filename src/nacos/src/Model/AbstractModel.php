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
namespace Hyperf\Nacos\Model;

use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Str;

abstract class AbstractModel implements Arrayable
{
    /**
     * @var array
     */
    public $requiredFields = [];

    public function __construct($config = [])
    {
        foreach ($config as $key => $value) {
            $key = Str::camel($key);
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function __toString()
    {
        return http_build_query($this->getParams());
    }

    public function getParams(): array
    {
        $params = array_filter(get_object_vars($this), function ($item) {
            return $item !== null;
        });
        unset($params['requiredFields']);
        $intersect = array_intersect(array_keys($params), $this->requiredFields);
        sort($this->requiredFields);
        sort($intersect);
        if ($intersect !== $this->requiredFields) {
            throw new \Exception('Missing key information ' . implode(',', $this->requiredFields));
        }

        return $params;
    }

    public function toJson(): string
    {
        return json_encode($this->getParams());
    }

    public function toArray(): array
    {
        return $this->getParams();
    }
}
