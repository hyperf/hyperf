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

namespace Hyperf\View;

use Psr\Http\Message\ResponseInterface;

interface RenderInterface
{
    public function render(string $template, array $data = []): ResponseInterface;

    public function getContents(string $template, array $data = []): string;

    public function getContentType(): string;
}
