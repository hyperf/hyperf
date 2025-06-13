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

namespace Hyperf\Database\Commands\Seeders;

use Hyperf\Command\Command;
use Hyperf\Database\Seeders\Seed;

/**
 * @property Seed $seed
 */
abstract class BaseCommand extends Command
{
    protected function getSeederPaths(): array
    {
        return array_merge(
            $this->seed->paths(),
            [$this->getSeederPath()]
        );
    }

    /**
     * Get seeder path (either specified by '--path' option or default location).
     */
    protected function getSeederPath(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? BASE_PATH . '/' . $targetPath
                : $targetPath;
        }

        return BASE_PATH . DIRECTORY_SEPARATOR . 'seeders';
    }

    /**
     * Determine if the given path(s) are pre-resolved "real" paths.
     */
    protected function usingRealPath(): bool
    {
        return $this->input->hasOption('realpath') && $this->input->getOption('realpath');
    }
}
