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

namespace Hyperf\JsonRpc;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Utils\Serializer\SymfonyNormalizer;
use Psr\Container\ContainerInterface;

class DataFormatterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var NormalizerInterface $normalizer */
        $normalizer = $container->get(NormalizerInterface::class);
        if ($normalizer instanceof SymfonyNormalizer) {
            return new NormalizeDataFormatter($normalizer);
        }
        return new DataFormatter();
    }
}
