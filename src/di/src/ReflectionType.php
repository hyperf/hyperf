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

namespace Hyperf\Di;

class ReflectionType
{
    public function __construct(private string $name, private bool $allowsNull = false, private array $metadata = [])
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    public function getMeta(string $key)
    {
        return $this->metadata[$key] ?? null;
    }
}
