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

namespace Hyperf\Validation\Rules;

class ImageFile extends File
{
    /**
     * Create a new image file rule instance.
     */
    public function __construct()
    {
        $this->rules('image');
    }

    /**
     * The dimension constraints for the uploaded file.
     */
    public function dimensions(Dimensions $dimensions): static
    {
        $this->rules($dimensions);

        return $this;
    }
}
