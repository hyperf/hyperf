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

trait AccessToken
{
    private ?string $accessToken = null;

    private int $expireTime = 0;

    public function getAccessToken(): ?string
    {
        $username = $this->config->getUsername();
        $password = $this->config->getPassword();

        if ($username === null || $password === null) {
            return null;
        }

        if (! $this->isExpired()) {
            return $this->accessToken;
        }

        $result = $this->handleResponse(
            $this->app->auth->login($username, $password)
        );

        $this->accessToken = $result['accessToken'];
        $this->expireTime = $result['tokenTtl'] + time();

        return $this->accessToken;
    }

    protected function isExpired(): bool
    {
        if (isset($this->accessToken) && $this->expireTime > time() + 60) {
            return false;
        }
        return true;
    }
}
