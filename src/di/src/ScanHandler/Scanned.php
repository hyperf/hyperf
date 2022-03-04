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
namespace Hyperf\Di\ScanHandler;

class Scanned
{
    /**
     * @var bool
     */
    protected $scanned;

    public function __construct(bool $scanned)
    {
        $this->scanned = $scanned;
    }

    public function isScanned(): bool
    {
        return $this->scanned;
    }
}
