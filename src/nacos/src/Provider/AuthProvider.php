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

namespace Hyperf\Nacos\Provider;

use GuzzleHttp\RequestOptions;
use Hyperf\Nacos\AbstractProvider;
use Psr\Http\Message\ResponseInterface;

class AuthProvider extends AbstractProvider
{
    public function login(string $username, string $password): ResponseInterface
    {
        return $this->client()->request('POST', 'nacos/v1/auth/users/login', [
            RequestOptions::QUERY => [
                'username' => $username,
            ],
            RequestOptions::FORM_PARAMS => [
                'password' => $password,
            ],
        ]);
    }
}
