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

namespace Hyperf\Breaker\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Breaker\Handler\TimeoutHandler;
use Hyperf\Breaker\Storage\MemoryStorage;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Breaker extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $handler = TimeoutHandler::class;

    /**
     * @var string
     */
    public $storage = MemoryStorage::class;

    /**
     * @var string
     */
    public $fallback;

    /**
     * @var float
     */
    public $timeout;

    /**
     * @var array
     */
    public $value;

    public function __construct($value = null)
    {
        parent::__construct($value);

        $this->value = $value ?? [];
    }
}
