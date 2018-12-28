<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di;

use Hyperf\Di\Aop\AstParserFactory;
use Hyperf\Di\Command\InitProxyCommand;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Parser::class => AstParserFactory::class,
                PrettyPrinterAbstract::class => Standard::class,
            ],
            'commands' => [
                InitProxyCommand::class,
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}
