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
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Exception\InvalidArgumentException;

class Json
{
    public static function encode($data, $options = JSON_UNESCAPED_UNICODE, int $depth = 512): string
    {
        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $json = json_encode($data, $options, $depth);

        static::handleJsonError(json_last_error(), json_last_error_msg());

        return $json;
    }

    public static function decode(string $json, $assoc = true, int $depth = 512, int $flags = 0)
    {
        $decode = json_decode($json, $assoc, $depth, $flags);

        static::handleJsonError(json_last_error(), json_last_error_msg());

        return $decode;
    }

    protected static function handleJsonError($lastError, $message)
    {
        if ($lastError === JSON_ERROR_NONE) {
            return;
        }

        throw new InvalidArgumentException($message, $lastError);
    }
}
