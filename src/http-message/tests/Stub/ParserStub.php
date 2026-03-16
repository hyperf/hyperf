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

namespace HyperfTest\HttpMessage\Stub;

use Hyperf\HttpMessage\Server\RequestParserInterface;

class ParserStub implements RequestParserInterface
{
    public function parse(string $rawBody, string $contentType): array
    {
        return [
            'mock' => true,
        ];
    }

    public function has(string $contentType): bool
    {
        return true;
    }
}
