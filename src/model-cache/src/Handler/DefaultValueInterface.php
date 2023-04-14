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
namespace Hyperf\ModelCache\Handler;

interface DefaultValueInterface
{
    public function defaultValue(mixed $primaryValue): mixed;

    public function isDefaultValue(mixed $data): bool;

    public function getPrimaryValue(mixed $data): mixed;
}
