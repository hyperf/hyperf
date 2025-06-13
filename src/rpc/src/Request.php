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

namespace Hyperf\Rpc;

class Request
{
    public function __construct(protected string $path, protected array $params, protected null|int|string $id = null, protected ?array $extra = null)
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setParams(array $params): static
    {
        $this->params = $params;
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getId(): null|int|string
    {
        return $this->id;
    }

    public function setExtra(?array $extra): void
    {
        $this->extra = $extra;
    }

    public function getExtra(): ?array
    {
        return $this->extra;
    }
}
