<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Consul;

use Hyperf\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Consul\Exception\ServerException;

/**
 * @method int getStatusCode()
 * @method string getReasonPhrase()
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
