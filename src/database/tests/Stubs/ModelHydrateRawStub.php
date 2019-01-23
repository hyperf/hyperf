<?php

namespace HyperfTest\Database\Stubs;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionInterface as Connection;
use Hyperf\Database\Model\Model;
use Mockery;

class ModelHydrateRawStub extends Model
{
    public static function hydrate(array $items, $connection = null)
    {
        return 'hydrated';
    }

    public function getConnection():ConnectionInterface
    {
        $mock = Mockery::mock(Connection::class);
        $mock->shouldReceive('select')->once()->with('SELECT ?', ['foo'])->andReturn([]);

        return $mock;
    }
}