<?php


namespace Hyperf\Dag;

use Hyperf\Dag\Exception\InvalidArgumentException;

class Dag implements Runner
{
    /**
     * @var array<Vertex>
     */
    protected $vertexes;

    /**
     * @var int
     */
    protected $concurrency;

    public function __construct(int $concurrency = 0)
    {
        $this->concurrency = $concurrency;
    }

    public function addVertex(Vertex $vertex): self {
        $this->vertexes[] = $vertex;
        return $this;
    }

    public function addEdge(Vertex $from, Vertex $to): self {
        $from->children[] = $to;
        $to->parents[] = $from;
        return $this;
    }

    public function run(): void {
        $all = $this->breathFirstSearchLayer();

        foreach ($all as $layer) {
            $callables = [];
            foreach ($layer as $vertex) {
                $callables[] = $vertex->value;
            }
            \parallel($callables, $this->concurrency);
        }
    }

    /**
     * @return array<array<Vertex>>
     */
    private function breathFirstSearchLayer(): array {
        $queue = $this->buildInitialQueue();
        $all = [];
        $visited = [];

        while (! $queue->isEmpty()) {
            $length = $queue->count();
            $tmp = [];
            for ($i = 0; $i < $length; $i++) {
                $element = $queue->dequeue();
                if (isset($visited[$element->key])) {
                    continue;
                }
                $visited[$element->key] = true;
                $tmp[] = $element;
                next: foreach ($element->children as $child) {
                    foreach($child->parents as $parent) {
                        if ($parent == $element) {
                            continue;
                        }
                        if (!isset($visited[$parent->key])) {
                            continue 2;
                        }
                    }
                    $queue->enqueue($child);
                }

            }
            $all[] = $tmp;
        }
        return $all;
    }

    private function buildInitialQueue() : \SplQueue {
        $roots = [];
        /** @var Vertex $vertex */
        foreach ($this->vertexes as $vertex) {
            if (empty($vertex->parents)) {
                $roots[] = $vertex;
            }
        }

        if (empty($roots)) {
            throw new InvalidArgumentException("no roots can be found in dag");
        }

        $queue = new \SplQueue();
        foreach ($roots as $root) {
            $queue->enqueue($root);
        }
        return $queue;
    }

}
