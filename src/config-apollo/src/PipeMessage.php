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
namespace Hyperf\ConfigApollo;

class PipeMessage
{
    /**
     * @var array
     */
    public $configurations;

    /**
     * @var string
     */
    public $releaseKey;

    /**
     * @var string
     */
    public $namespace;

    public function __construct($data)
    {
        if (isset($data['configurations'], $data['releaseKey'], $data['namespace'])) {
            $this->configurations = $data['configurations'];
            $this->releaseKey = $data['releaseKey'];
            $this->namespace = $data['namespace'];
        }
    }

    public function isValid(): bool
    {
        if (! $this->configurations || ! $this->releaseKey || ! $this->namespace) {
            return false;
        }
        return true;
    }
}
