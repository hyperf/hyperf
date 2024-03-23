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

namespace Hyperf\Scout\Engine;

use Elasticsearch\Client;
use Elasticsearch\Client as Elastic;
use Hyperf\Collection\Collection as BaseCollection;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Scout\Builder;
use Hyperf\Scout\SearchableInterface;
use Throwable;

use function Hyperf\Collection\collect;

class ElasticsearchEngine extends Engine
{
    /**
     * Elastic server version.
     */
    public static ?string $version = null;

    /**
     * Index where the models will be saved.
     */
    protected ?string $index = null;

    /**
     * Create a new engine instance.
     *
     * @param Elastic $elastic elastic where the instance of Elastic|\Elasticsearch\Client is stored
     */
    public function __construct(protected Client $elastic, ?string $index = null)
    {
        $this->index = $this->initIndex($elastic, $index);
    }

    /**
     * Update the given model in the index.
     *
     * @phpstan-ignore-next-line
     * @param Collection<int, \Hyperf\Database\Model\Model&\Hyperf\Scout\Searchable> $models
     */
    public function update($models): void
    {
        $params['body'] = [];
        $models->each(function ($model) use (&$params) {
            if ($this->index) {
                $update = [
                    '_id' => $model->getKey(),
                    '_index' => $this->index,
                    '_type' => $model->searchableAs(),
                ];
            } else {
                $update = [
                    '_id' => $model->getKey(),
                    '_index' => $model->searchableAs(),
                    ...$this->appendType(),
                ];
            }
            $params['body'][] = ['update' => $update];
            $params['body'][] = [
                'doc' => $model->toSearchableArray(),
                'doc_as_upsert' => true,
            ];
        });
        $this->elastic->bulk($params);
    }

    /**
     * Remove the given model from the index.
     *
     * @phpstan-ignore-next-line
     * @param Collection<int, \Hyperf\Database\Model\Model&\Hyperf\Scout\Searchable> $models
     */
    public function delete($models): void
    {
        $params['body'] = [];
        $models->each(function ($model) use (&$params) {
            if ($this->index) {
                $delete = [
                    '_id' => $model->getKey(),
                    '_index' => $this->index,
                    '_type' => $model->searchableAs(),
                ];
            } else {
                $delete = [
                    '_id' => $model->getKey(),
                    '_index' => $model->searchableAs(),
                    ...$this->appendType(),
                ];
            }
            $params['body'][] = ['delete' => $delete];
        });
        $this->elastic->bulk($params);
    }

    /**
     * Perform the given search on the engine.
     *
     * @return mixed
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            'numericFilters' => $this->filters($builder),
            'size' => $builder->limit,
        ]));
    }

    /**
     * Perform the given search on the engine.
     *
     * @param int $perPage
     * @param int $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $result = $this->performSearch($builder, [
            'numericFilters' => $this->filters($builder),
            'from' => (($page * $perPage) - $perPage),
            'size' => $perPage,
        ]);
        $result['nbPages'] = $this->getTotalCount($result) / $perPage;
        return $result;
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param mixed $results
     */
    public function mapIds($results): BaseCollection
    {
        return (new Collection($results['hits']['hits']))->pluck('_id')->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param mixed $results
     * @param Model|SearchableInterface $model
     */
    public function map(Builder $builder, $results, $model): Collection
    {
        if ($this->getTotalCount($results) === 0) {
            return $model->newCollection();
        }
        $keys = collect($results['hits']['hits'])->pluck('_id')->values()->all();
        return $model->getScoutModelsByIds(
            $builder,
            $keys
        )->filter(function ($model) use ($keys) {
            return in_array($model->getScoutKey(), $keys);
        });
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param mixed $results
     */
    public function getTotalCount($results): int
    {
        $total = $results['hits']['total'];
        if (is_array($total)) {
            return $results['hits']['total']['value'];
        }

        return $total;
    }

    /**
     * Flush all the model's records from the engine.
     */
    public function flush(Model $model): void
    {
        // @phpstan-ignore-next-line
        $model->newQuery()
            ->orderBy($model->getKeyName())
            ->unsearchable();
    }

    protected function initIndex(Client $client, ?string $index): ?string
    {
        if (! static::$version) {
            try {
                static::$version = $client->info()['version']['number'];
            } catch (Throwable $exception) {
                static::$version = '0.0.0';
            }
        }

        // When the version of elasticsearch is more than 7.0.0, it does not support type, so set `null` to `$index`.
        if (version_compare(static::$version, '7.0.0', '<')) {
            return $index;
        }

        return null;
    }

    /**
     * Perform the given search on the engine.
     *
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $params = [
            'index' => $this->index,
            'type' => $builder->index ?: $builder->model->searchableAs(),
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [['query_string' => ['query' => "*{$builder->query}*"]]],
                    ],
                ],
            ],
        ];
        if (! $this->index) {
            unset($params['type']);
            $params['index'] = $builder->index ?: $builder->model->searchableAs();
        }
        if ($sort = $this->sort($builder)) {
            $params['body']['sort'] = $sort;
        }
        if (isset($options['from'])) {
            $params['body']['from'] = $options['from'];
        }
        if (isset($options['size'])) {
            $params['body']['size'] = $options['size'];
        }
        if (isset($options['numericFilters']) && count($options['numericFilters'])) {
            $params['body']['query']['bool']['must'] = array_merge(
                $params['body']['query']['bool']['must'],
                $options['numericFilters']
            );
        }
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $this->elastic,
                $builder->query,
                $params
            );
        }
        return $this->elastic->search($params);
    }

    /**
     * Get the filter array for the query.
     *
     * @return array
     */
    protected function filters(Builder $builder)
    {
        return collect($builder->wheres)->map(function ($value, $key) {
            if (is_array($value)) {
                return ['terms' => [$key => $value]];
            }
            return ['match_phrase' => [$key => $value]];
        })->values()->all();
    }

    /**
     * Generates the sort if theres any.
     *
     * @param Builder $builder
     * @return null|array
     */
    protected function sort($builder)
    {
        if (count($builder->orders) == 0) {
            return null;
        }
        return collect($builder->orders)->map(function ($order) {
            return [$order['column'] => $order['direction']];
        })->toArray();
    }

    protected function appendType(): array
    {
        if (version_compare(static::$version, '7.0.0', '<')) {
            return [
                '_type' => 'doc',
            ];
        }

        return [];
    }
}
