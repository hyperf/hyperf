<?php


namespace Hyperf\Dag;


use Hyperf\Dag\Exception\InvalidArgumentException;

class Vertex
{
    /**
     * @var string
     */
    public $key;
    /**
     * @var callable
     */
    public $value;
    /**
     * @var array<Vertex>
     */
    public $parents = [];
    /**
     * @var array<Vertex>
     */
    public $children = [];

    private static $jobId = 0;

    public static function make($job, string $key = null): self {
        if ($key === null) {
            $key = (string) (++self::$jobId);
        }

        $v = new Vertex();
        $v->key = $key;

        if (is_callable($job)) {
            $v->value = $job;
            return $v;
        }

        if ($job instanceof Runner) {
            $v->value = [$job, "run"];
            return $v;
        }

        throw new InvalidArgumentException("$job is not a callable nor implements Runner");
    }
}
