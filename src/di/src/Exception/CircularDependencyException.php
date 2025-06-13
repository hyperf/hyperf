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

namespace Hyperf\Di\Exception;

use RuntimeException;

class CircularDependencyException extends RuntimeException
{
    protected array $list = [];

    protected bool $sealed = false;

    public function addDefinitionName(string $name)
    {
        if ($this->sealed) {
            return;
        }

        if (in_array($name, $this->list)) {
            $this->sealed = true;
        }

        $this->updateListAndMessage($name);
    }

    private function updateListAndMessage(string $name)
    {
        array_unshift($this->list, $name);
        $listAsString = implode(' -> ', $this->list);
        $this->message = "depth limit reached due to the following dependencies: {$listAsString}";
    }
}
