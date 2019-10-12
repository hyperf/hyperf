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
    public static $jsonErrorMessages = [
        'JSON_ERROR_DEPTH' => 'The maximum stack depth has been exceeded.',
        'JSON_ERROR_STATE_MISMATCH' => 'Invalid or malformed JSON.',
        'JSON_ERROR_CTRL_CHAR' => 'Control character error, possibly incorrectly encoded.',
        'JSON_ERROR_SYNTAX' => 'Syntax error.',
        'JSON_ERROR_UTF8' => 'Malformed UTF-8 characters, possibly incorrectly encoded.', // PHP 5.3.3
        'JSON_ERROR_RECURSION' => 'One or more recursive references in the value to be encoded.', // PHP 5.5.0
        'JSON_ERROR_INF_OR_NAN' => 'One or more NAN or INF values in the value to be encoded', // PHP 5.5.0
        'JSON_ERROR_UNSUPPORTED_TYPE' => 'A value of a type that cannot be encoded was given', // PHP 5.5.0
    ];

    public static function encode($data, $options = JSON_UNESCAPED_UNICODE)
    {
        set_error_handler(function () {
            static::handleJsonError(JSON_ERROR_SYNTAX);
        }, E_WARNING);

        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            $data = json_encode($data->toArray(), $options);
        }

        $json = json_encode($data, $options);
        restore_error_handler();
        static::handleJsonError(json_last_error());
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
        static::handleJsonError(json_last_error());

        return $decode;
    }

    protected static function handleJsonError($lastError)
    {
        if ($lastError === JSON_ERROR_NONE) {
            return;
        }

        $availableErrors = [];
        foreach (static::$jsonErrorMessages as $const => $message) {
            if (defined($const)) {
                $availableErrors[constant($const)] = $message;
            }
        }

        if (isset($availableErrors[$lastError])) {
            throw new InvalidArgumentException($availableErrors[$lastError], $lastError);
        }

        throw new InvalidArgumentException('Unknown JSON encoding/decoding error.');
    }
}
