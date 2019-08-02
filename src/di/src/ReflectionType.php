<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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

    /**
     * ReflectionType constructor.
     * @param string $name
     * @param bool $allowsNull
     * @param array $metadata
     */
    public function __construct(string $name, bool $allowsNull = false, array $metadata = [])
    {
        $this->name = $name;
        $this->allowsNull = $allowsNull;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getMeta(string $key)
    {
        return $this->metadata[$key] ?? null;
    }
}
