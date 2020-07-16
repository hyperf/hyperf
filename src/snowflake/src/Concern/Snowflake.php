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
namespace Hyperf\Snowflake\Concern;

use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;

trait Snowflake
{
    public function creating()
    {
        if (! $this->getKey()) {
            $container = ApplicationContext::getContainer();
            $generator = $container->get(IdGeneratorInterface::class);
            $this->{$this->getKeyName()} = $generator->generate();
        }
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'int';
    }
}
