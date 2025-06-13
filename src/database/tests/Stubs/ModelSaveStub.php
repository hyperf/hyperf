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

namespace HyperfTest\Database\Stubs;

use Hyperf\Context\Context;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionInterface as Connection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;
use Mockery;

class ModelSaveStub extends Model
{
    protected ?string $table = 'save_stub';

    protected array $guarded = ['id'];

    public function save(array $options = []): bool
    {
        Context::set('__model.saved', true);
        return true;
    }

    public function setIncrementing($value)
    {
        $this->incrementing = $value;
    }

    public function getConnection(): ConnectionInterface
    {
        $mock = Mockery::mock(Connection::class);
        $mock->shouldReceive('getQueryGrammar')->andReturn(Mockery::mock(Grammar::class));
        $mock->shouldReceive('getPostProcessor')->andReturn(Mockery::mock(Processor::class));
        $mock->shouldReceive('getName')->andReturn('name');

        return $mock;
    }
}
