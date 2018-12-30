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

namespace Hyperf\Event\Exception;

class ExceptionAggregate extends RuntimeException implements ExceptionInterface
{
    /**
     * @var array
     */
    private $exceptions = [];

    public static function fromExceptions(array $exceptions): self
    {
        $e = new self('One or more listeners raised an exception during notification');
        $e->exceptions = $exceptions;
        return $e;
    }

    public function getListenerExceptions(): array
    {
        return $this->exceptions;
    }
}
