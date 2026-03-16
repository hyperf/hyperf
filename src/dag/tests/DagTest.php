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

namespace HyperfTest\Dag;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Dag\Dag;
use Hyperf\Dag\Vertex;
use Hyperf\Engine\Channel;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DagTest extends TestCase
{
    public function testComplexExample()
    {
        $dag = new Dag();
        $chan = new Channel(1);
        $a = Vertex::make(function () use ($chan) {
            $chan->push('A');
        });
        $b = Vertex::make(function () use ($chan) {
            $chan->push('B');
        });
        $c = Vertex::make(function () use ($chan) {
            $chan->push('C');
        });
        $d = Vertex::make(function () use ($chan) {
            $chan->push('D');
        });
        $e = Vertex::make(function () use ($chan) {
            $chan->push('E');
        });
        $f = Vertex::make(function () use ($chan) {
            $chan->push('F');
        });
        $g = Vertex::make(function () use ($chan) {
            $chan->push('G');
        });
        $h = Vertex::make(function () use ($chan) {
            $chan->push('H');
        });
        $i = Vertex::make(function () use ($chan) {
            $chan->push('I');
        });
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addVertex($d)
            ->addVertex($e)
            ->addVertex($f)
            ->addVertex($g)
            ->addVertex($h)
            ->addVertex($i)
            ->addEdge($a, $i)
            ->addEdge($a, $i)
            ->addEdge($a, $b)
            ->addEdge($a, $c)
            ->addEdge($a, $d)
            ->addEdge($b, $h)
            ->addEdge($b, $e)
            ->addEdge($b, $f)
            ->addEdge($c, $f)
            ->addEdge($c, $g)
            ->addEdge($d, $g)
            ->addEdge($e, $i)
            ->addEdge($f, $i)
            ->addEdge($h, $i)
            ->addEdge($g, $i);
        Coroutine::create(function () use ($dag) {
            $dag->run();
        });

        $expected = ['A', 'B', 'C', 'D', 'H', 'E', 'F', 'G', 'I'];
        foreach ($expected as $e) {
            $data = $chan->pop();
            $this->assertEquals($e, $data);
        }
    }

    public function testAccessResults()
    {
        $a = Vertex::make(function () {
            return 1;
        }, 'a');
        $b = Vertex::make(function ($results) use ($a) {
            return $results[$a->key] + 1;
        }, 'b');
        $dag = new Dag();
        $dag->addVertex($a)->addVertex($b)->addEdge($a, $b);
        $result = $dag->run();
        $this->assertEquals(1, $result['a']);
        $this->assertEquals(2, $result['b']);

        $parent = new Dag();
        $results = $parent->addVertex(Vertex::of($dag, 'nest'))->run();
        $this->assertEquals(['a' => 1, 'b' => 2], $results['nest']);
    }

    public function testRunWithRace()
    {
        $fastChan = new Channel(1);
        $slowChan = new Channel(2);
        $a = Vertex::make(function () use ($fastChan) {
            $fastChan->push(0);
        });
        $b = Vertex::make(function () use ($fastChan) {
            $fastChan->push(1);
        });
        $c = Vertex::make(function () use ($fastChan) {
            $fastChan->push(2);
        });
        $d = Vertex::make(function () use ($slowChan) {
            $slowChan->push(3);
        });
        $dag = new Dag();
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addVertex($d)
            ->addEdge($a, $b)
            ->addEdge($b, $c)
            ->addEdge($a, $d);
        Coroutine::create(function () use ($dag) {
            $dag->run();
        });
        $data = $fastChan->pop();
        $this->assertEquals(0, $data);
        $data = $fastChan->pop();
        $this->assertEquals(1, $data);
        $data = $fastChan->pop();
        $this->assertEquals(2, $data);
        $data = $slowChan->pop();
        $this->assertEquals(3, $data);
    }

    public function testRun()
    {
        $chan = new Channel(1);
        $a = Vertex::make(function () use ($chan) {
            $chan->push(0);
        });
        $b = Vertex::make(function () use ($chan) {
            $chan->push(0);
        });
        $c = Vertex::make(function () use ($chan) {
            $chan->push(1);
        });
        $d = Vertex::make(function () use ($chan) {
            $chan->push(1);
        });
        $dag = new Dag();
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addVertex($d)
            ->addEdge($c, $a)
            ->addEdge($d, $b);
        Coroutine::create(function () use ($dag) {
            $dag->run();
        });
        $data = $chan->pop();
        $this->assertEquals(1, $data);
        $data = $chan->pop();
        $this->assertEquals(1, $data);
        $data = $chan->pop();
        $this->assertEquals(0, $data);
        $data = $chan->pop();
        $this->assertEquals(0, $data);

        $a = Vertex::make(function () use ($chan) {
            $chan->push(0);
        });
        $b = Vertex::make(function () use ($chan) {
            $chan->push(1);
        });
        $c = Vertex::make(function () use ($chan) {
            $chan->push(1);
        });
        $d = Vertex::make(function () use ($chan) {
            $chan->push(1);
        });
        $dag = new Dag();
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addVertex($d)
            ->addEdge($c, $a)
            ->addEdge($d, $a)
            ->addEdge($b, $a);
        Coroutine::create(function () use ($dag) {
            $dag->run();
        });
        $data = $chan->pop();
        $this->assertEquals(1, $data);
        $data = $chan->pop();
        $this->assertEquals(1, $data);
        $data = $chan->pop();
        $this->assertEquals(1, $data);
        $data = $chan->pop();
        $this->assertEquals(0, $data);

        $e = Vertex::make(function () use ($chan) {
            $chan->push(2);
        });
        $f = Vertex::of($dag);
        $nestedDag = new Dag();
        $nestedDag->addVertex($e)->addVertex($f)->addEdge($e, $f);
        Coroutine::create(function () use ($nestedDag) {
            $nestedDag->run();
        });
        $data = $chan->pop();
        $this->assertEquals(2, $data);
        $data = $chan->pop();
        $this->assertEquals(1, $data);
        $data = $chan->pop();
        $this->assertEquals(1, $data);
        $data = $chan->pop();
        $this->assertEquals(1, $data);
        $data = $chan->pop();
        $this->assertEquals(0, $data);
    }

    public function testCheckCircularDependences()
    {
        $key = 1;
        $dag = new Dag();
        $a = Vertex::make(function () {echo "A\n"; }, (string) $key++);
        $b = Vertex::make(function () {echo "B\n"; }, (string) $key++);
        $c = Vertex::make(function () {echo "C\n"; }, (string) $key++);
        $d = Vertex::make(function () {echo "D\n"; }, (string) $key++);
        $e = Vertex::make(function () {echo "E\n"; }, (string) $key++);
        $f = Vertex::make(function () {echo "F\n"; }, (string) $key++);
        $g = Vertex::make(function () {echo "G\n"; }, (string) $key++);
        $h = Vertex::make(function () {echo "H\n"; }, (string) $key++);
        $i = Vertex::make(function () {echo "I\n"; }, (string) $key++);
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addVertex($d)
            ->addVertex($e)
            ->addVertex($f)
            ->addVertex($g)
            ->addVertex($h)
            ->addVertex($i)
            ->addEdge($a, $b)
            ->addEdge($b, $c)
            ->addEdge($c, $a)
            ->addEdge($d, $e)
            ->addEdge($e, $f)
            ->addEdge($f, $d)
            ->addEdge($g, $h)
            ->addEdge($h, $i)
            ->addEdge($i, $g);

        $ret = $dag->checkCircularDependencies();

        $this->assertEquals(['3', '2', '1'], $ret[0]);
        $this->assertEquals(['6', '5', '4'], $ret[1]);
        $this->assertEquals(['9', '8', '7'], $ret[2]);
    }
}
