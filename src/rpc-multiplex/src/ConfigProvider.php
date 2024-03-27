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

namespace Hyperf\RpcMultiplex;

use Hyperf\RpcMultiplex\Contract\HostReaderInterface;
use Hyperf\RpcMultiplex\Contract\HttpMessageBuilderInterface;
use Hyperf\RpcMultiplex\HttpMessage\HostReader\NullHostReader;
use Hyperf\RpcMultiplex\Listener\RegisterProtocolListener;
use Multiplex\Contract\IdGeneratorInterface;
use Multiplex\Contract\PackerInterface;
use Multiplex\Contract\SerializerInterface;
use Multiplex\IdGenerator;
use Multiplex\Packer;
use Multiplex\Serializer\StringSerializer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                IdGeneratorInterface::class => IdGenerator::class,
                SerializerInterface::class => StringSerializer::class,
                PackerInterface::class => Packer::class,
                HttpMessageBuilderInterface::class => HttpMessageBuilder::class,
                HostReaderInterface::class => NullHostReader::class,
            ],
            'listeners' => [
                RegisterProtocolListener::class,
            ],
        ];
    }
}
