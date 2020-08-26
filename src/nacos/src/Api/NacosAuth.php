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

use GuzzleHttp\RequestOptions;
use Hyperf\Utils\Codec\Json;

class NacosAuth extends AbstractNacos
{
    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var int
     */
    private $tokenTtl = 0;

    public function getAccessToken(): string
    {
        if (empty($this->config->get('nacos.username'))) {
            return '';
        }
        if (! empty($this->accessToken) && $this->tokenTtl > time() + 60) {
            return $this->accessToken;
        }
        $resultArr = $this->login();
        $this->accessToken = $resultArr['accessToken'];
        $this->tokenTtl = $resultArr['tokenTtl'] + time();
        return $this->accessToken;
    }

    public function login(): array
    {
        $response = $this->client()->request('POST', '/nacos/v1/auth/users/login', [
            RequestOptions::FORM_PARAMS => [
                'username' => $this->config->get('nacos.username'),
                'password' => $this->config->get('nacos.password'),
            ],
        ]);
        return Json::decode($response->getBody()->getContents());
    }
}
