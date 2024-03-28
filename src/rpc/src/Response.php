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

class Response
{
    public function __construct(protected null|int|string $id, protected mixed $result)
    {
    }

    public function getId(): null|int|string
    {
        return $this->id;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): static
    {
        $this->result = $result;
        return $this;
    }
}
