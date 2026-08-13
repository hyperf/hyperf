# DAG

`hyperf/dag` is a lightweight Directed Acyclic Graph (**DAG**) task orchestration library.

## Scenarios

Suppose we have a series of tasks to execute:

- If there are dependencies between them, they can be executed sequentially.
- If they are not dependent on each other, we can choose to execute them concurrently to speed up execution.
- There is an intermediate state between the two: some tasks have dependencies, while others can be executed concurrently.

We can abstract this third complex scenario into a `DAG` to solve it.

## Installation

```bash
composer require hyperf/dag
```

## Example

Assuming we have a series of tasks with the topology shown in the image above, where vertices represent tasks and edges represent dependencies. (B, C, and D can only be completed after A is completed; H, E, and F can only be completed after B is completed...)

Through `hyperf/dag`, you can build and execute a `DAG` in the following way:

```php
<?php
$dag = new \Hyperf\Dag\Dag();
$a = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "A\n";});
$b = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "B\n";});
$c = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "C\n";});
$d = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "D\n";});
$e = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "E\n";});
$f = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "F\n";});
$g = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "G\n";});
$h = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "H\n";});
$i = \Hyperf\Dag\Vertex::make(function() {sleep(1); echo "I\n";});
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
    
// Needs to be executed in a coroutine environment
$dag->run(); 
```

Output:

```php
// after 1s
A
// after 2s
D
C
B
// after 3s
G
F
E
H
// after 4s
I
```

> DAG will schedule tasks as early as possible. Try adjusting B's execution time to 2 seconds, and you will find that B and G finish together.

## Accessing Preceding Results

Each task can receive an array parameter containing the results of all preceding dependencies. After the `DAG` executes, it will also return an array of the same structure, containing the execution results of each step.

```php
<?php
$dag = new \Hyperf\Dag\Dag();
$a = \Hyperf\Dag\Vertex::make(function() {return 1;});
$b = \Hyperf\Dag\Vertex::make(function($results) use ($a) {
    return $results[$a->key] + 1;
});
$results = $dag->addVertex($a)->addVertex($b)->addEdge($a, $b)->run();
assert($results[$a->key] === 1);
assert($results[$b->key] === 2);
```

## Defining a Task

In the document above, we used a closure to define a task. The format is as follows:

```php
// The second parameter of Vertex::make is an optional parameter, which serves as the key of the vertex, which is the key of the result array.
\Hyperf\Dag\Vertex::make(function() { return 'hello'; }, "greeting");
```

In addition to using closure functions to define tasks, you can also use classes that implement the `\Hyperf\Dag\Runner` interface and convert them into a vertex using `Vertex::of`.

```php
class MyJob implements \Hyperf\Dag\Runner {
    public function run($results = []) {
        return 'hello';
    }
}

\Hyperf\Dag\Vertex::of(new MyJob(), "greeting");
```

`\Hyperf\Dag\Dag` itself also implements the `\Hyperf\Dag\Runner` interface, so it can be nested.

```php
<?php
// Namespace omitted
$a = Vertex::make(function () { return 1;});
$b = Vertex::make(function () { return 2;});
$c = Vertex::make(function () { return 3;});

$nestedDag = new Dag();
$nestedDag->addVertex($a)->addVertex($b)->addEdge($a, $b);
$d = Vertex::of($nestedDag);

$superDag = new Dag();
$superDag->addVertex($c)->addVertex($d)->addEdge($c, $d);
$superDag->run();
```

## Controlling Concurrency

The `\Hyperf\Dag\Dag` class provides the `setConcurrency(int n)` method to control the maximum concurrency. The default is 10.

