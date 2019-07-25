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

namespace Hyperf\Di\Aop;

class RewriteCollection
{
    protected $rewriteOnlyMethods = [];

    protected $rewriteAllMethods = false;

    public function __construct(bool $rewriteAllMethods)
    {
        $this->rewriteAllMethods = $rewriteAllMethods;
    }

    /**
     * @return array
     */
    public function getRewriteOnlyMethods(): array
    {
        return $this->rewriteOnlyMethods;
    }

    /**
     * @param array $rewriteOnlyMethods
     * @return RewriteCollection
     */
    public function setRewriteOnlyMethods(array $rewriteOnlyMethods): RewriteCollection
    {
        $this->rewriteOnlyMethods = $rewriteOnlyMethods;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRewriteAllMethods(): bool
    {
        return $this->rewriteAllMethods;
    }

    /**
     * @param bool $rewriteAllMethods
     * @return RewriteCollection
     */
    public function setRewriteAllMethods(bool $rewriteAllMethods): RewriteCollection
    {
        $this->rewriteAllMethods = $rewriteAllMethods;
        return $this;
    }
}
