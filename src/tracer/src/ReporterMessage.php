<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer;

class ReporterMessage
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $spans;

    /**
     * XMessage constructor.
     * @param array $options
     * @param array $spans
     */
    public function __construct(
        array $options = [],
        array $spans = []
    ) {
        $this->options = $options;
        $this->spans = $spans;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getSpans(): array
    {
        return $this->spans;
    }
}
