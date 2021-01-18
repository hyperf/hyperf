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
namespace Hyperf\Watcher\Ast;

class Metadata
{
    /**
     * @var string
     */
    public $namespace;

    /**
     * @var string
     */
    public $className;

    /**
     * @var string
     */
    public $path;

    public function isClass(): bool
    {
        return $this->className !== null;
    }

    public function toClassName(): string
    {
        return $this->namespace . '\\' . $this->className;
    }
}
