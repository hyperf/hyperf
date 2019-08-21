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

namespace Hyperf\Snowflake;

interface IdGeneratorInterface extends \Hyperf\Contract\IdGeneratorInterface
{
    const DEFAULT_SECOND = 1565712000;

    public function generate(?Meta $meta = null): int;

    public function degenerate(int $id): Meta;
}
