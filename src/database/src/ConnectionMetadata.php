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

namespace Hyperf\Database;

use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;

/**
 * SQL compilation settings. Creating metadata must not execute database IO.
 */
class ConnectionMetadata
{
    /** @param class-string<Builder> $builderClass */
    public function __construct(
        public array $config,
        public Grammar $grammar,
        public Processor $processor,
        public string $database,
        public string $prefix = '',
        public string $builderClass = Builder::class
    ) {
        $this->grammar->setTablePrefix($prefix);
    }
}
