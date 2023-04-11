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
namespace Hyperf\ViewEngine;

use Hyperf\Context\ApplicationContext;
use Hyperf\View\Engine\EngineInterface;
use Hyperf\ViewEngine\Contract\FactoryInterface;

class HyperfViewEngine implements EngineInterface
{
    public function render(string $template, array $data, array $config): string
    {
        /** @var FactoryInterface $factory */
        $factory = ApplicationContext::getContainer()->get(FactoryInterface::class);

        return $factory->make($template, $data)->render();
    }
}
