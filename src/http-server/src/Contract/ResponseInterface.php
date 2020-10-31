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
namespace Hyperf\HttpServer\Contract;

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Contracts\Xmlable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface
{
    /**
     * Format data to JSON and return data with Content-Type:application/json header.
     *
     * @param array|Arrayable|Jsonable $data
     */
    public function json($data): PsrResponseInterface;

    /**
     * Format data to XML and return data with Content-Type:application/xml header.
     *
     * @param array|Arrayable|Xmlable $data
     * @param string $root the name of the root node
     */
    public function xml($data, string $root = 'root'): PsrResponseInterface;

    /**
     * Format data to a string and return data with Content-Type:text/plain header.
     * @param mixed $data
     */
    public function raw($data): PsrResponseInterface;

    /**
     * Redirect to a URL.
     */
    public function redirect(string $toUrl, int $status = 302, string $schema = 'http'): PsrResponseInterface;

    /**
     * Create a file download response.
     *
     * @param string $file the file path which want to send to client
     * @param string $name the alias name of the file that client receive
     */
    public function download(string $file, string $name = ''): PsrResponseInterface;

    /**
     * Override a response with a cookie.
     */
    public function withCookie(Cookie $cookie): ResponseInterface;
}
