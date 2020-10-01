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
namespace Hyperf\Utils\Codec;

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
        $disableLibxmlEntityLoader = libxml_disable_entity_loader(true);
        $respObject = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);
        libxml_disable_entity_loader($disableLibxmlEntityLoader);
        if ($respObject === false) {
            throw new InvalidArgumentException('Syntax error.');
        }

        return json_decode(json_encode($respObject), true);
    }
}
