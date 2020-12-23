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

        try {
            $json = json_encode($data, $options | JSON_THROW_ON_ERROR, $depth);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $json;
    }

    public static function decode(string $json, $assoc = true)
    {
        try {
            $decode = json_decode($json, $assoc, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $decode;
    }
}
