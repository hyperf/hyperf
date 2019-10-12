<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Utils;

use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Xmlable;
use Hyperf\Utils\Exception\InvalidArgumentException;
use SimpleXMLElement;

class Xml
{
    public static function toXml($data, $parentNode = null, $root = 'root')
    {
        if ($data instanceof Xmlable) {
            return (string) $data;
        }
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } else {
            $data = (array) $data;
        }
        if ($parentNode === null) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?>' . "<{$root}></{$root}>");
        } else {
            $xml = $parentNode;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::toXml($value, $xml->addChild($key));
            } else {
                if (is_numeric($key)) {
                    $xml->addChild('item' . $key, (string) $value);
                } else {
                    $xml->addChild($key, (string) $value);
                }
            }
        }
        return trim($xml->asXML());
    }

    public static function toArray($xml)
    {
        /*if(!parserXML($resp)){
            return false;
         }*/
        $disableLibxmlEntityLoader = libxml_disable_entity_loader(true);
        // 如果希望使用多个libxml选项，可以使用管道将它们分开，如下所示
        // 如果不加 LIBXML_NOERROR 选项的话传入错误的xml字符串会抛出错误，加上这个选项会返回 false。或者在函数前面加上 @ 操作符也会返回false
        // 也可以直接调用上面判断是否是一个标准的xml格式
        $respObject = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);
        libxml_disable_entity_loader($disableLibxmlEntityLoader);
        if ($respObject === false) {
            throw new InvalidArgumentException('Invalid Xml data.');
        }

        return json_decode(json_encode($respObject), true);
    }
}
