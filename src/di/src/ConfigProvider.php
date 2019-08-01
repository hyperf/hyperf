<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di;

use Hyperf\Di\Command\InitProxyCommand;
use Hyperf\Di\Listener\BootApplicationListener;
use kuiper\docReader\DocReader;
use kuiper\docReader\DocReaderInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DocReaderInterface::class => DocReader::class,
                MethodDefinitionCollectorInterface::class => class_exists(DocReader::class)
                    ? DocMethodDefinitionCollector::class : MethodDefinitionCollector::class,
            ],
            'commands' => [
                InitProxyCommand::class,
            ],
            'listeners' => [
                BootApplicationListener::class,
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
