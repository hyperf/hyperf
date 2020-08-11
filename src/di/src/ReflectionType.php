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
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $allowsNull;

    /**
     * @var array
     */
    private $metadata;

    public function __construct(string $name, bool $allowsNull = false, array $metadata = [])
    {
        $this->name = $name;
        $this->allowsNull = $allowsNull;
        $this->metadata = $metadata;
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
