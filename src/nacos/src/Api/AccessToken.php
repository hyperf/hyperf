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
namespace Hyperf\Nacos\Api;

trait AccessToken
{
    /**
     * @var null|string
     */
    private $accessToken;

    /**
     * @var int
     */
    private $expireTime = 0;

    public function getAccessToken(): ?string
    {
        $username = $this->config->get('nacos.username');
        $password = $this->config->get('nacos.password');

        if ($username === null || $password === null) {
            return null;
        }

        if (isset($this->accessToken) && $this->expireTime > time() + 60) {
            return $this->accessToken;
        }

        $api = $this->container->get(NacosAuth::class);

        $result = $api->login($username, $password);

        $this->accessToken = $result['accessToken'];
        $this->expireTime = $result['tokenTtl'] + time();

        return $this->accessToken;
    }
}
