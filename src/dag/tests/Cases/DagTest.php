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
namespace HyperfTest\Cases;

use Hyperf\Dag\Dag;
use Hyperf\Dag\Exception\InvalidArgumentException;
use Hyperf\Dag\Vertex;
use Hyperf\Engine\Channel;
use Hyperf\Utils\Coroutine;
use PHPUnit\Framework\TestCase;

/**
 * @internalcomp
 * @coversNothing
 */
class DagTest extends TestCase
{
    public function testComplexExample() {
        $dag = new Dag();
        $chan = new Channel(1);
        $a = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('A');});
        $b = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('B');});
        $c = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('C');});
        $d = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('D');});
        $e = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('E');});
        $f = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('F');});
        $g = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('G');});
        $h = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('H');});
        $i = \Hyperf\Dag\Vertex::make(function() use ($chan) {usleep(1000); $chan->push('I');});
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
            ->addEdge($h, $i)
            ->addEdge($e, $i)
            ->addEdge($f, $i)
            ->addEdge($g, $i);
        Coroutine::create(function() use ($dag) {
            $dag->run();
        });

        $expected = ['A', 'D', 'C', 'B', 'G', 'F', 'E', 'H', 'I'];
        foreach ($expected as $e) {
            $data = $chan->pop();
            $this->assertEquals($e, $data);
        }
    }
    public function testRun() {
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
        $f = Vertex::make($dag);
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
    public function testBreathFirstSearch()
    {
        $a = Vertex::make(function () {}, 'a');
        $b = Vertex::make(function () {}, 'b');;
        $c = Vertex::make(function () {}, 'c');
        $dag = new Dag();
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addEdge($a, $b)
            ->addEdge($b, $c);
        $result = $this->breathFirstSearch($dag);
        $this->assertCount(3, $result);
        $this->assertEquals('a', $result[0][0]->key);
        $this->assertEquals('b', $result[1][0]->key);
        $this->assertEquals('c', $result[2][0]->key);

        $a = Vertex::make(function () {}, 'a');
        $b = Vertex::make(function () {}, 'b');;
        $c = Vertex::make(function () {}, 'c');
        $dag = new Dag();
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c);
        $result = $this->breathFirstSearch($dag);
        $this->assertCount(1, $result);
        $this->assertEquals('a', $result[0][0]->key);
        $this->assertEquals('b', $result[0][1]->key);
        $this->assertEquals('c', $result[0][2]->key);

        $a = Vertex::make(function () {}, 'a');
        $b = Vertex::make(function () {}, 'b');;
        $c = Vertex::make(function () {}, 'c');
        $dag = new Dag();
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addEdge($a, $c)
            ->addEdge($a, $b);
        $result = $this->breathFirstSearch($dag);
        $this->assertEquals('a', $result[0][0]->key);
        $this->assertEquals('c', $result[1][0]->key);
        $this->assertEquals('b', $result[1][1]->key);

        $a = Vertex::make(function () {}, 'a');
        $b = Vertex::make(function () {}, 'b');;
        $c = Vertex::make(function () {}, 'c');
        $dag = new Dag();
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addEdge($a, $c)
            ->addEdge($b, $c);
        $result = $this->breathFirstSearch($dag);
        $this->assertEquals('a', $result[0][0]->key);
        $this->assertEquals('b', $result[0][1]->key);
        $this->assertEquals('c', $result[1][0]->key);

        $this->expectException(InvalidArgumentException::class);
        $a = Vertex::make(function () {}, 'a');
        $b = Vertex::make(function () {}, 'b');;
        $c = Vertex::make(function () {}, 'c');
        $dag = new Dag();
        $dag->addVertex($a)
            ->addVertex($b)
            ->addVertex($c)
            ->addEdge($a, $b)
            ->addEdge($b, $c)
            ->addEdge($c, $a);
        $this->breathFirstSearch($dag);
    }

    private function breathFirstSearch($dag): array {
        $ref = new \ReflectionClass($dag);
        $method = $ref->getMethod("breathFirstSearchLayer");
        $method->setAccessible(true);
        return $method->invoke($dag);
    }
}
