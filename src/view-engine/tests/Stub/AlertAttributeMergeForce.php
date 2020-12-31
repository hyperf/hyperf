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

use Hyperf\Utils\ApplicationContext;
use Hyperf\ViewEngine\Component\Component;
use Hyperf\ViewEngine\Contract\FactoryInterface;

class AlertAttributeMergeForce extends Component
{
    public $message;

    public $type;

    public function __construct($message, $type)
    {
        $this->message = $message;
        $this->type = $type;
    }

    public function render()
    {
        $factory = ApplicationContext::getContainer()
            ->get(FactoryInterface::class);

        return $factory->make('components.alert-4');
    }
}
