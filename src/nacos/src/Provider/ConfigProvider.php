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
use JetBrains\PhpStorm\ArrayShape;
use Psr\Http\Message\ResponseInterface;

class ConfigProvider extends AbstractProvider
{
    public const WORD_SEPARATOR = "\x02";

    public const LINE_SEPARATOR = "\x01";

    public function get(string $dataId, string $group, ?string $tenant = null): ResponseInterface
    {
        return $this->request('GET', 'nacos/v1/cs/configs', [
            RequestOptions::QUERY => $this->filter([
                'dataId' => $dataId,
                'group' => $group,
                'tenant' => $tenant,
            ]),
        ]);
    }

    public function set(string $dataId, string $group, string $content, ?string $type = null, ?string $tenant = null): ResponseInterface
    {
        return $this->request('POST', 'nacos/v1/cs/configs', [
            RequestOptions::FORM_PARAMS => $this->filter([
                'dataId' => $dataId,
                'group' => $group,
                'tenant' => $tenant,
                'type' => $type,
                'content' => $content,
            ]),
        ]);
    }

    public function delete(string $dataId, string $group, ?string $tenant = null): ResponseInterface
    {
        return $this->request('DELETE', 'nacos/v1/cs/configs', [
            RequestOptions::QUERY => $this->filter([
                'dataId' => $dataId,
                'group' => $group,
                'tenant' => $tenant,
            ]),
        ]);
    }

    public function listener(
        #[ArrayShape([
            'dataId' => 'string',
            'group' => 'string',
            'contentMD5' => 'string', // md5(file_get_contents($configPath))
            'tenant' => 'string',
        ])]
        array $options = []
    ): ResponseInterface {
        $config = ($options['dataId'] ?? null) . self::WORD_SEPARATOR .
            ($options['group'] ?? null) . self::WORD_SEPARATOR .
            ($options['contentMD5'] ?? null) . self::WORD_SEPARATOR .
            ($options['tenant'] ?? null) . self::LINE_SEPARATOR;
        return $this->request('POST', 'nacos/v1/cs/configs/listener', [
            RequestOptions::QUERY => [
                'Listening-Configs' => $config,
            ],
            RequestOptions::HEADERS => [
                'Long-Pulling-Timeout' => 30,
            ],
        ]);
    }
}
