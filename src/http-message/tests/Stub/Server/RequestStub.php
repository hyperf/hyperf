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
namespace HyperfTest\HttpMessage\Stub\Server;

use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Server\RequestParserInterface;

class RequestStub extends Request
{
    public static function setParser(?RequestParserInterface $parser)
    {
        static::$parser = $parser;
    }

    public static function getParser(): RequestParserInterface
    {
        return parent::getParser();
    }
}
