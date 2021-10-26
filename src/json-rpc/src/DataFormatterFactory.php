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
namespace Hyperf\JsonRpc;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Rpc\Context;
use Hyperf\Utils\Serializer\Serializer;
use Hyperf\Utils\Serializer\SymfonyNormalizer;
use Psr\Container\ContainerInterface;

class DataFormatterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var NormalizerInterface $normalizer */
        $normalizer = $container->get(NormalizerInterface::class);
        $context = $container->get(Context::class);
        if ($normalizer instanceof SymfonyNormalizer || $normalizer instanceof Serializer) {
            return new NormalizeDataFormatter($normalizer, $context);
        }
        return new DataFormatter($context);
    }
}
