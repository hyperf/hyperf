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

namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Exception\BadRequestHttpException;
use Hyperf\HttpMessage\Server\Request\JsonParser;
use Hyperf\HttpMessage\Server\Request\XmlParser;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RequestParserTest extends TestCase
{
    public function testJsonParserFailed()
    {
        $this->expectException(BadRequestHttpException::class);
        $parser = new JsonParser();
        $parser->parse('{"hy"', '');
    }

    public function testXmlParserFailed()
    {
        $this->expectException(BadRequestHttpException::class);
        $parser = new XmlParser();
        $parser->parse('{"hy"', '');
    }
}
