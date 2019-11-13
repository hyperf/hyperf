<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Utils;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use Hyperf\Utils\Serializer\SymfonyNormalizer;
use Symfony\Component\Serializer\Serializer;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => value(function () {
                if (class_exists(Serializer::class)) {
                    return [
                        NormalizerInterface::class => SymfonyNormalizer::class,
                        Serializer::class => SerializerFactory::class,
                    ];
                }
                return [
                    NormalizerInterface::class => SimpleNormalizer::class,
                ];
            }),
        ];
    }
}
