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

namespace Hyperf\HttpMessage\Server\Request;

use Hyperf\HttpMessage\Exception\BadRequestHttpException;
use Hyperf\HttpMessage\Server\RequestParserInterface;
use Hyperf\Utils\Exception\InvalidArgumentException;
use Hyperf\Utils\Json;

class JsonParser implements RequestParserInterface
{
    /**
     * @var bool
     */
    public $asArray = true;

    /**
     * @var bool
     */
    public $throwException = true;

    public function parse(string $rawBody, string $contentType): array
    {
        try {
            $parameters = Json::decode($rawBody, $this->asArray);
            return is_array($parameters) ? $parameters : [];
        } catch (InvalidArgumentException $e) {
            if ($this->throwException) {
                throw new BadRequestHttpException('Invalid JSON data in request body: ' . $e->getMessage());
            }
            return [];
        }
    }

    public function has(string $contentType): bool
    {
        return true;
    }
}
