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
namespace Hyperf\Utils\Exception;

class ParallelExecutionException extends \RuntimeException
{
    /**
     * @var array
     */
    private $results;

    /**
     * @var array
     */
    private $throwables;

    public function getResults()
    {
        return $this->results;
    }

    public function setResults(array $results)
    {
        $this->results = $results;
    }

    public function getThrowables()
    {
        return $this->throwables;
    }

    public function setThrowables(array $throwables)
    {
        return $this->throwables = $throwables;
    }
}
