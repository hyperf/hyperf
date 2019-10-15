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
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Exception\InvalidArgumentException;

class Json
{
    public static function encode($data, $options = JSON_UNESCAPED_UNICODE)
    {
        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            $data = json_encode($data->toArray(), $options);
        }

        $json = json_encode($data, $options);
        restore_error_handler();
        static::handleJsonError(json_last_error(), json_last_error_msg());
        return $json;
    }

    public static function decode($json, $asArray = true)
    {
        if (is_array($json)) {
            throw new InvalidArgumentException('Invalid JSON data.');
        }
        if ($json === null || $json === '') {
            return null;
        }
        $decode = json_decode((string) $json, $asArray);
        static::handleJsonError(json_last_error(), json_last_error_msg());
        return $decode;
    }

    protected static function handleJsonError($lastError, $lastErrorMsg)
    {
        if ($lastError === JSON_ERROR_NONE) {
            return;
        }
        throw new InvalidArgumentException($lastErrorMsg, $lastError);
    }
}
