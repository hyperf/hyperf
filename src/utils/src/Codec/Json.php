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
    /**
     * @param mixed $data
     * @throws InvalidArgumentException
     */
    public static function encode($data, int $flags = JSON_UNESCAPED_UNICODE, int $depth = 512): string
    {
        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        try {
            $json = json_encode($data, $flags | JSON_THROW_ON_ERROR, $depth);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $json;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function decode(string $json, bool $assoc = true, int $depth = 512, int $flags = 0)
    {
        try {
            $decode = json_decode($json, $assoc, $depth, $flags | JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $decode;
    }
}
