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

namespace Hyperf\ViewEngine\Engine;

use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\ViewEngine\Contract\EngineInterface;

class FileEngine implements EngineInterface
{
    /**
     * Create a new file engine instance.
     */
    public function __construct(protected Filesystem $files)
    {
    }

    /**
     * Get the evaluated contents of the view.
     */
    public function get(string $path, array $data = []): string
    {
        return $this->files->get($path);
    }
}
