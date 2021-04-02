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
namespace Hyperf\Crontab\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Crontabs extends AbstractAnnotation
{
    /**
     * @var array
     */
    public $crontabs = [];

    public function __construct($value = null)
    {
        $this->bindMainProperty('crontabs', $value);
    }

    public function collectClass(string $className): void
    {
        /** @var Crontab $crontab */
        foreach ($this->crontabs as $key => $crontab) {
            $crontab->rule = str_replace('\\', '', $crontab->rule);
            if (! $crontab->name) {
                $crontab->name = $className . '_' . $key;
            }

            if (! $crontab->callback) {
                throw new \InvalidArgumentException(sprintf('Missing argument $callback of @Crontab annotation.'));
            }
            if (is_string($crontab->callback)) {
                $crontab->callback = [$className, $crontab->callback];
            }
        }

        parent::collectClass($className);
    }
}
