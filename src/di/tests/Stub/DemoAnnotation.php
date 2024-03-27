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

namespace HyperfTest\Di\Stub;

use Hyperf\Di\Annotation\AbstractAnnotation;

class DemoAnnotation extends AbstractAnnotation
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
