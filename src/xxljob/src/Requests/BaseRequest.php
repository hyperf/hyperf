<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Requests;

class BaseRequest
{
    public static function create(array $data = []): static
    {
        $obj = new static();
        foreach ($data as $k => $v) {
            if (property_exists($obj, $k)) {
                $obj->{$k} = $v;
            }
        }
        return $obj;
    }
}
