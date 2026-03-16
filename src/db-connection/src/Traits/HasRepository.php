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

namespace Hyperf\DbConnection\Traits;

use Hyperf\Database\Model\Model;
use RuntimeException;

trait HasRepository
{
    /**
     * @var string the full namespace of repository class
     */
    protected $repository;

    /**
     * @throws RuntimeException when the model does not define the repository class
     */
    public function getRepository()
    {
        if (! $this->repository || ! class_exists($this->repository) && ! interface_exists($this->repository)) {
            throw new RuntimeException(sprintf('Cannot detect the repository of %s', static::class));
        }
        return $this->getContainer()->get($this->repository);
    }
}
