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

namespace HyperfTest\ViewEngine\Stub;

use Hyperf\Context\ApplicationContext;
use Hyperf\ViewEngine\Component\Component;
use Hyperf\ViewEngine\Contract\FactoryInterface;

class Alert extends Component
{
    public function __construct(public $message)
    {
    }

    public function render(): mixed
    {
        $factory = ApplicationContext::getContainer()
            ->get(FactoryInterface::class);

        return $factory->make('components.alert');
    }
}
