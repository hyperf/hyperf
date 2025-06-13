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

namespace Hyperf\ViewEngine\Concern;

use Hyperf\ViewEngine\Blade;

trait ManagesTranslations
{
    /**
     * The translation replacements for the translation being rendered.
     */
    protected array $translationReplacements = [];

    /**
     * Start a translation block.
     */
    public function startTranslation(array $replacements = [])
    {
        ob_start();

        $this->translationReplacements = $replacements;
    }

    /**
     * Render the current translation.
     *
     * @return string
     */
    public function renderTranslation()
    {
        return Blade::container()->make('translator')->get(
            trim(ob_get_clean()),
            $this->translationReplacements
        );
    }
}
