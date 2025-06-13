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

namespace Hyperf\Snowflake;

interface IdGeneratorInterface extends \Hyperf\Contract\IdGeneratorInterface
{
    /**
     * Generate an ID by meta, if meta is null, then use the default meta.
     */
    public function generate(?Meta $meta = null): int;

    /**
     * Degenerate the meta by ID.
     */
    public function degenerate(int $id): Meta;
}
