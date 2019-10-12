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

namespace HyperfTest\Utils;

use Hyperf\Utils\Xml;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class XmlTest extends TestCase
{
    public function testToArray()
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        $data = [
            'return_code' => 'SUCCESS',
            'return_msg' => 'OK',
        ];
        $this->assertSame($data, Xml::toArray($xml));
    }

    /**
     * @expectedException \Hyperf\Utils\Exception\InvalidArgumentException
     * @expectedExceptionMessage Syntax error.
     */
    public function testToArrayException()
    {
        $xml = 'xxxxx';
        $data = [
            'return_code' => 'SUCCESS',
            'return_msg' => 'OK',
        ];
        $this->assertSame($data, Xml::toArray($xml));
    }

    public function testToXml()
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        $data = [
            'return_code' => 'SUCCESS',
            'return_msg' => 'OK',
        ];
        $this->assertSame(Xml::toXml(Xml::toArray($xml), null, 'xml'), Xml::toXml($data, null, 'xml'));
    }
}
