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

use Hyperf\HttpMessage\Server\RequestParserInterface;
use InvalidArgumentException;

use function Hyperf\Support\make;

class Parser implements RequestParserInterface
{
    protected array $parsers = [];

    public function __construct()
    {
        $jsonParser = make(JsonParser::class);
        $xmlParser = make(XmlParser::class);

        $this->parsers = [
            'application/json' => $jsonParser,
            'text/json' => $jsonParser,
            'application/xml' => $xmlParser,
            'text/xml' => $xmlParser,
        ];
    }

    public function parse(string $rawBody, string $contentType): array
    {
        $contentType = strtolower($contentType);
        if (! array_key_exists($contentType, $this->parsers)) {
            throw new InvalidArgumentException("The '{$contentType}' request parser is not defined.");
        }

        $parser = $this->parsers[$contentType];
        if (! $parser instanceof RequestParserInterface) {
            throw new InvalidArgumentException("The '{$contentType}' request parser is invalid. It must implement the Hyperf\\HttpMessage\\Server\\RequestParserInterface.");
        }

        return $parser->parse($rawBody, $contentType);
    }

    public function has(string $contentType): bool
    {
        return array_key_exists(strtolower($contentType), $this->parsers);
    }
}
