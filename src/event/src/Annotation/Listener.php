<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Event\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Listener extends AbstractAnnotation
{
    /**
     * @var int
     */
    public $priority = 1;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['priority']) && is_numeric($value['priority'])) {
            $this->priority = (int) $value['priority'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collectClass(string $className, ?string $target): void
    {
        if ($this->value !== null) {
            AnnotationCollector::collectClass($className, static::class, [
                'priority' => $this->priority,
            ]);
        }
    }
}
