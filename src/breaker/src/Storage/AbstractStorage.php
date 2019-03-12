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

namespace Hyperf\Breaker\Storage;

use Hyperf\Breaker\State;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var State
     */
    protected $state;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->state = make(State::class);
    }

    public function getState(): State
    {
        return $this->state;
    }
}
