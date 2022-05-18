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
namespace Hyperf\Validation\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Validation\SceneCollector;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Scene extends AbstractAnnotation
{
    public $scene;

    public function __construct(...$value)
    {
        parent::__construct(...$value);
        $this->bindMainProperty('scene', $value);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        SceneCollector::set($className . ':' . $target, $this->scene);
    }
}
