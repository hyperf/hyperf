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
namespace Hyperf\Consul;

use Hyperf\Consul\Exception\ServerException;
use Hyperf\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @method int getStatusCode()
 * @method string getReasonPhrase()
 * @method StreamInterface getBody()
 */
class ConsulResponse
{
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function __call($name, $arguments)
    {
        return $this->response->{$name}(...$arguments);
    }

    public function json(string $key = null, $default = null)
    {
        if ($this->response->getHeaderLine('Content-Type') !== 'application/json') {
            throw new ServerException('The Content-Type of response is not equal application/json');
        }
        $data = json_decode((string) $this->response->getBody(), true);
        if (! $key) {
            return $data;
        }
        return Arr::get($data, $key, $default);
    }
}
