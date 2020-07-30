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
namespace Hyperf\Config\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Value extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $key;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('key', $value);
    }
}
