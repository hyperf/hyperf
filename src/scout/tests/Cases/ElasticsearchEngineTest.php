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

namespace HyperfTest\Scout\Cases;

use Elasticsearch\Client;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Scout\Builder;
use Hyperf\Scout\Engine\ElasticsearchEngine;
use HyperfTest\Scout\Stub\ContainerStub;
use HyperfTest\Scout\Stub\ElasticsearchEngineTestModel;
use HyperfTest\Scout\Stub\SearchableModel;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ElasticsearchEngineTest extends TestCase
{
    protected function setUp(): void
    {
        ContainerStub::mockContainer();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        $this->assertTrue(true);
        ContainerStub::unsetContainer();
    }

    public function testUpdateAddsObjectsToIndex()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('bulk')->with([
            'body' => [
                [
                    'update' => [
                        '_id' => 1,
                        '_index' => 'scout',
                        '_type' => 'table',
                    ],
                ],
                [
                    'doc' => ['id' => 1],
                    'doc_as_upsert' => true,
                ],
            ],
        ]);
        $engine = new ElasticsearchEngine($client, 'scout');
        $engine->update(Collection::make([new ElasticsearchEngineTestModel()]));
    }

    public function testDeleteRemovesObjectsToIndex()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('bulk')->with([
            'body' => [
                [
                    'delete' => [
                        '_id' => 1,
                        '_index' => 'scout',
                        '_type' => 'table',
                    ],
                ],
            ],
        ]);
        $engine = new ElasticsearchEngine($client, 'scout');
        $engine->delete(Collection::make([new ElasticsearchEngineTestModel()]));
    }

    public function testSearchSendsCorrectParametersToElasticsearch()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('search')->with([
            'index' => 'scout',
            'type' => 'table',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['query_string' => ['query' => '*zonda*']],
                            ['match_phrase' => ['foo' => 1]],
                            ['terms' => ['bar' => [1, 3]]],
                        ],
                    ],
                ],
                'sort' => [
                    ['id' => 'desc'],
                ],
            ],
        ]);
        $engine = new ElasticsearchEngine($client, 'scout');
        $builder = new Builder(new ElasticsearchEngineTestModel(), 'zonda');
        $builder->where('foo', 1);
        $builder->where('bar', [1, 3]);
        $builder->orderBy('id', 'desc');
        $engine->search($builder);
    }

    public function testBuilderCallbackCanManipulateSearchParametersToElasticsearch()
    {
        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')->with(['modified_by_callback']);
        $engine = new ElasticsearchEngine($client, 'scout');
        $builder = new Builder(
            new ElasticsearchEngineTestModel(),
            'huayra',
            function (Client $client, $query, $params) {
                $this->assertNotEmpty($params);
                $this->assertEquals('huayra', $query);
                $params = ['modified_by_callback'];
                return $client->search($params);
            }
        );
        $engine->search($builder);
    }

    public function testGetScoutModelsByIds()
    {
        $model = new SearchableModel();
        $query = Mockery::mock(\Hyperf\Database\Model\Builder::class);
        $query->shouldReceive('whereIn->get')->andReturn(new Collection([
            new SearchableModel(['id' => 1]),
            new SearchableModel(['id' => 2]),
        ]));
        $model->setQueryCallback(static function () use ($query) {
            return $query;
        });
        $builder = Mockery::mock(Builder::class);
        $res = $model->getScoutModelsByIds($builder, [2, 1]);
        $this->assertSame([['id' => 2], ['id' => 1]], $res->toArray());
    }

    public function testMapCorrectlyMapsResultsToModels()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $engine = new ElasticsearchEngine($client, 'scout');
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getScoutKey')->andReturn('1');
        $model->shouldReceive('getScoutModelsByIds')->once()->with($builder, ['1'])->andReturn($models = Collection::make([$model]));
        $model->shouldReceive('newCollection')->andReturn($models);
        $results = $engine->map($builder, [
            'hits' => [
                'total' => 1,
                'hits' => [
                    [
                        '_id' => '1',
                    ],
                ],
            ],
        ], $model);
        $this->assertEquals(1, count($results));
    }

    public function testGetTotalCount()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $engine = new ElasticsearchEngine($client, 'scout');
        $this->assertSame(1, $engine->getTotalCount([
            'hits' => [
                'total' => 1,
            ],
        ]));
        $this->assertSame(2, $engine->getTotalCount([
            'hits' => [
                'total' => [
                    'value' => 2,
                    'relation' => 'eq',
                ],
            ],
        ]));
    }
}
