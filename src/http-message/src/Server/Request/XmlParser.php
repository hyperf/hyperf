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

namespace Hyperf\HttpMessage\Server\Request;

use Hyperf\Codec\Xml;
use Hyperf\HttpMessage\Exception\BadRequestHttpException;
use Hyperf\HttpMessage\Server\RequestParserInterface;
use InvalidArgumentException;

class XmlParser implements RequestParserInterface
{
    public bool $throwException = true;

    public function parse(string $rawBody, string $contentType): array
    {
        try {
            return Xml::toArray($rawBody);
        } catch (InvalidArgumentException $e) {
            if ($this->throwException) {
                throw new BadRequestHttpException('Invalid XML data in request body: ' . $e->getMessage());
            }
            return [];
        }
    }

    public function has(string $contentType): bool
    {
        return true;
    }
}
