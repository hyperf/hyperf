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
namespace Hyperf\Utils;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use Symfony\Component\Serializer\Serializer;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                Serializer::class => SerializerFactory::class,
                NormalizerInterface::class => SimpleNormalizer::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
