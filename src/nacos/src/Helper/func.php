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
if (! function_exists('is_json_str')) {
    function is_json_str($string)
    {
        json_decode($string);
        return json_last_error() == JSON_ERROR_NONE;
    }
}

function get_class_method_params_name($object, $method)
{
    $ref = new \ReflectionMethod($object, $method);
    $params_name = [];
    foreach ($ref->getParameters() as $item) {
        $params_name[] = $item->getName();
    }

    return $params_name;
}

function xml2array($xml_string)
{
    if (strpos($xml_string, '<') === false) {
        return [];
    }

    return (array) @simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
}
