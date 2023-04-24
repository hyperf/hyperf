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
namespace Hyperf\Serializer;

use Hyperf\Contract\NormalizerInterface;
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
        ];
    }
}
