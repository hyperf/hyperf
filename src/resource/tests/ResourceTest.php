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

namespace HyperfTest\Resource;

use Hyperf\Collection\Collection;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Resource\Concerns\ConditionallyLoadsAttributes;
use Hyperf\Resource\Json\JsonResource;
use Hyperf\Resource\Value\MergeValue;
use Hyperf\Resource\Value\MissingValue;
use HyperfTest\Resource\Stubs\Models\Author;
use HyperfTest\Resource\Stubs\Models\Post;
use HyperfTest\Resource\Stubs\Models\Subscription;
use HyperfTest\Resource\Stubs\Resources\AuthorResourceWithOptionalRelationship;
use HyperfTest\Resource\Stubs\Resources\ObjectResource;
use HyperfTest\Resource\Stubs\Resources\PostCollectionResource;
use HyperfTest\Resource\Stubs\Resources\PostResource;
use HyperfTest\Resource\Stubs\Resources\PostResourceWithExtraData;
use HyperfTest\Resource\Stubs\Resources\PostResourceWithOptionalData;
use HyperfTest\Resource\Stubs\Resources\PostResourceWithOptionalMerging;
use HyperfTest\Resource\Stubs\Resources\PostResourceWithOptionalPivotRelationship;
use HyperfTest\Resource\Stubs\Resources\PostResourceWithOptionalRelationship;
use HyperfTest\Resource\Stubs\Resources\PostResourceWithoutWrap;
use HyperfTest\Resource\Stubs\Resources\ReallyEmptyPostResource;
use HyperfTest\Resource\Stubs\Resources\ResourceWithPreservedKeys;
use PHPUnit\Framework\Attributes\CoversNothing;

use function Hyperf\Collection\collect;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ResourceTest extends TestCase
{
    public function testResourcesMayBeConvertedToJson()
    {
        $this->http(function () {
            return (new PostResource(new Post([
                'id' => 5,
                'title' => 'Test Title',
                'abstract' => 'Test abstract',
            ])))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'title' => 'Test Title',
                'custom' => true,
            ],
        ]);
    }

    public function testResourcesMayBeConvertedToJsonWithToJsonMethod()
    {
        $resource = new PostResource(new Post([
            'id' => 5,
            'title' => 'Test Title',
            'abstract' => 'Test abstract',
        ]));

        $this->assertSame('{"id":5,"title":"Test Title","custom":true}', $resource->toJson());
    }

    public function testAnObjectsMayBeConvertedToJson()
    {
        $this->http(function () {
            return ObjectResource::make(
                (object) ['first_name' => 'Bob', 'age' => 40]
            )->toResponse();
        })->assertJson([
            'data' => [
                'name' => 'Bob',
                'age' => 40,
            ],
        ]);
    }

    public function testArraysWithObjectsMayBeConvertedToJson()
    {
        $this->http(function () {
            $objects = [
                (object) ['first_name' => 'Bob', 'age' => 40],
                (object) ['first_name' => 'Jack', 'age' => 25],
            ];
            return ObjectResource::collection($objects)->toResponse();
        })->assertJson([
            'data' => [
                [
                    'name' => 'Bob',
                    'age' => 40,
                ],
                [
                    'name' => 'Jack',
                    'age' => 25,
                ],
            ],
        ]);
    }

    public function testResourcesMayHaveNoWrap()
    {
        $this->http(function () {
            return (new PostResourceWithoutWrap(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ])))->toResponse();
        })->assertJson([
            'id' => 5,
            'title' => 'Test Title',
            'custom' => true,
        ]);
    }

    public function testResourcesMayHaveOptionalValues()
    {
        $this->http(function () {
            return (new PostResourceWithOptionalData(new Post([
                'id' => 5,
            ])))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'second' => 'value',
                'third' => 'value',
                'fourth' => 'default',
                'fifth' => 'default',
            ],
        ]);
    }

    public function testResourcesMayHaveOptionalMerges()
    {
        $this->http(function () {
            return (new PostResourceWithOptionalMerging(new Post([
                'id' => 5,
            ])))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'second' => 'value',
            ],
        ]);
    }

    public function testResourcesMayHaveOptionalRelationships()
    {
        $this->http(function () {
            return (new PostResourceWithOptionalRelationship(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ])))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
            ],
        ]);
    }

    public function testResourcesMayLoadOptionalRelationships()
    {
        $this->http(function () {
            $post = new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]);

            $post->setRelation('author', new Author(['name' => 'jrrmartin']));

            return (new PostResourceWithOptionalRelationship($post))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'author' => ['name' => 'jrrmartin'],
                'author_name' => 'jrrmartin',
            ],
        ]);
    }

    public function testResourcesMayShowsNullForLoadedRelationshipWithValueNull()
    {
        $this->http(function () {
            $post = new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]);

            $post->setRelation('author', null);

            return (new PostResourceWithOptionalRelationship($post))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'author' => null,
                'author_name' => null,
            ],
        ]);
    }

    public function testResourcesMayHaveOptionalRelationshipsWithDefaultValues()
    {
        $this->http(function () {
            return (new AuthorResourceWithOptionalRelationship(new Author([
                'name' => 'jrrmartin',
            ])))->toResponse();
        })->assertJson([
            'data' => [
                'name' => 'jrrmartin',
                'posts_count' => 'not loaded',
                'latest_post_title' => 'not loaded',
            ],
        ]);
    }

    public function testResourcesMayHaveOptionalPivotRelationships()
    {
        $this->http(function () {
            $post = new Post(['id' => 5]);
            $post->setRelation('pivot', new Subscription());

            return (new PostResourceWithOptionalPivotRelationship($post))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'subscription' => [
                    'foo' => 'bar',
                ],
            ],
        ]);
    }

    public function testResourcesMayHaveOptionalPivotRelationshipsWithCustomAccessor()
    {
        $this->http(function () {
            $post = new Post(['id' => 5]);
            $post->setRelation('accessor', new Subscription());

            return (new PostResourceWithOptionalPivotRelationship($post))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'custom_subscription' => [
                    'foo' => 'bar',
                ],
            ],
        ]);
    }

    public function testResourcesMayCustomizeExtraData()
    {
        $this->http(function () {
            return (new PostResourceWithExtraData(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ])))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'title' => 'Test Title',
                'custom' => true,
            ],
            'foo' => 'bar',
        ]);
    }

    public function testResourcesMayCustomizeExtraDataWhenBuildingResponse()
    {
        $this->http(function () {
            return (new PostResourceWithExtraData(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ])))->additional(['baz' => 'qux'])->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'title' => 'Test Title',
                'custom' => true,
            ],
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
    }

    public function testCollectionsAreNotDoubledWrapped()
    {
        $this->http(function () {
            return (new PostCollectionResource(collect([new Post([
                'id' => 5,
                'title' => 'Test Title',
            ])])))->toResponse();
        })->assertJson([
            'data' => [
                [
                    'id' => 5,
                    'title' => 'Test Title',
                    'custom' => true,
                ],
            ],
        ]);
    }

    public function testPaginatorsReceiveLinks()
    {
        $this->http(function () {
            $paginator = new LengthAwarePaginator(
                collect([new Post(['id' => 5, 'title' => 'Test Title'])]),
                10,
                15,
                1
            );

            return (new PostCollectionResource($paginator))->toResponse();
        })->assertJson([
            'data' => [
                [
                    'id' => 5,
                    'title' => 'Test Title',
                    'custom' => true,
                ],
            ],
            'links' => [
                'first' => '/?page=1',
                'last' => '/?page=1',
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'path' => '/',
                'per_page' => 15,
                'to' => 1,
                'total' => 10,
            ],
        ]);
    }

    public function testToJsonMayBeLeftOffOfSingleResource()
    {
        $this->http(function () {
            return (new ReallyEmptyPostResource(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ])))->toResponse();
        })->assertJson([
            'data' => [
                'id' => 5,
                'title' => 'Test Title',
            ],
        ]);
    }

    public function testCollectionResourcesAreCountable()
    {
        $posts = collect([
            new Post(['id' => 1, 'title' => 'Test title']),
            new Post(['id' => 2, 'title' => 'Test title 2']),
        ]);

        $collection = new PostCollectionResource($posts);

        $this->assertCount(2, $collection);
        $this->assertSame(2, count($collection));
    }

    public function testKeysArePreservedIfTheResourceIsFlaggedToPreserveKeys()
    {
        $data = [
            'authorBook' => [
                'byId' => [
                    1 => [
                        'id' => 1,
                        'authorId' => 5,
                        'bookId' => 22,
                    ],
                    2 => [
                        'id' => 2,
                        'authorId' => 5,
                        'bookId' => 15,
                    ],
                    3 => [
                        'id' => 3,
                        'authorId' => 42,
                        'bookId' => 12,
                    ],
                ],
                'allIds' => [1, 2, 3],
            ],
        ];

        $this->http(function () use ($data) {
            return (new ResourceWithPreservedKeys($data))->toResponse();
        })->assertJson(['data' => $data]);
    }

    public function testKeysArePreservedInAnAnonymousCollectionIfTheResourceIsFlaggedToPreserveKeys()
    {
        $data = Collection::make([
            [
                'id' => 1,
                'authorId' => 5,
                'bookId' => 22,
            ],
            [
                'id' => 2,
                'authorId' => 5,
                'bookId' => 15,
            ],
            [
                'id' => 3,
                'authorId' => 42,
                'bookId' => 12,
            ],
        ])->keyBy->id;

        $this->http(function () use ($data) {
            return ResourceWithPreservedKeys::collection($data)->toResponse();
        })->assertJson(['data' => $data->toArray()]);
    }

    public function testLeadingMergeKeyedValueIsMergedCorrectly()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                return $this->filter([
                    new MergeValue(['name' => 'mohamed', 'location' => 'hurghada']),
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            'name' => 'mohamed', 'location' => 'hurghada',
        ], $results);
    }

    public function testLeadingMergeKeyedValueIsMergedCorrectlyWhenFirstValueIsMissing()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                return $this->filter([
                    new MergeValue([
                        0 => new MissingValue(),
                        'name' => 'mohamed',
                        'location' => 'hurghada',
                    ]),
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            'name' => 'mohamed', 'location' => 'hurghada',
        ], $results);
    }

    public function testLeadingMergeValueIsMergedCorrectly()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                return $this->filter([
                    new MergeValue(['First', 'Second']),
                    'Taylor',
                    'Mohamed',
                    new MergeValue(['Adam', 'Matt']),
                    'Jeffrey',
                    new MergeValue(['Abigail', 'Lydia']),
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            'First', 'Second', 'Taylor', 'Mohamed', 'Adam', 'Matt', 'Jeffrey', 'Abigail', 'Lydia',
        ], $results);
    }

    public function testMergeValuesMayBeMissing()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                return $this->filter([
                    new MergeValue(['First', 'Second']),
                    'Taylor',
                    'Mohamed',
                    $this->mergeWhen(false, ['Adam', 'Matt']),
                    'Jeffrey',
                    new MergeValue(['Abigail', 'Lydia']),
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            'First', 'Second', 'Taylor', 'Mohamed', 'Jeffrey', 'Abigail', 'Lydia',
        ], $results);
    }

    public function testInitialMergeValuesMayBeMissing()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                return $this->filter([
                    $this->mergeWhen(false, ['First', 'Second']),
                    'Taylor',
                    'Mohamed',
                    $this->mergeWhen(true, ['Adam', 'Matt']),
                    'Jeffrey',
                    new MergeValue(['Abigail', 'Lydia']),
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            'Taylor', 'Mohamed', 'Adam', 'Matt', 'Jeffrey', 'Abigail', 'Lydia',
        ], $results);
    }

    public function testMergeValueCanMergeJsonSerializable()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                $postResource = new PostResource(new Post([
                    'id' => 1,
                    'title' => 'Test Title 1',
                ]));

                return $this->filter([
                    new MergeValue($postResource),
                    'user' => 'test user',
                    'age' => 'test age',
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            'id' => 1,
            'title' => 'Test Title 1',
            'custom' => true,
            'user' => 'test user',
            'age' => 'test age',
        ], $results);
    }

    public function testMergeValueCanMergeCollectionOfJsonSerializable()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                $posts = collect([
                    new Post(['id' => 1, 'title' => 'Test title 1']),
                    new Post(['id' => 2, 'title' => 'Test title 2']),
                ]);

                return $this->filter([
                    new MergeValue(PostResource::collection($posts)),
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            ['id' => 1, 'title' => 'Test title 1', 'custom' => true],
            ['id' => 2, 'title' => 'Test title 2', 'custom' => true],
        ], $results);
    }

    public function testAllMergeValuesMayBeMissing()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                return $this->filter([
                    $this->mergeWhen(false, ['First', 'Second']),
                    'Taylor',
                    'Mohamed',
                    $this->mergeWhen(false, ['Adam', 'Matt']),
                    'Jeffrey',
                    $this->mergeWhen(false, ['Abigail', 'Lydia']),
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            'Taylor', 'Mohamed', 'Jeffrey',
        ], $results);
    }

    public function testNestedMerges()
    {
        $filter = new class {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                return $this->filter([
                    $this->mergeWhen(true, [['Something']]),
                    [
                        $this->mergeWhen(true, ['First', $this->mergeWhen(true, ['Second'])]),
                        'Third',
                    ],
                    [
                        'Fourth',
                    ],
                ]);
            }
        };

        $results = $filter->work();

        $this->assertEquals([
            [
                'Something',
            ],
            [
                'First', 'Second', 'Third',
            ],
            [
                'Fourth',
            ],
        ], $results);
    }

    public function testTheResourceCanBeAnArray()
    {
        $this->assertJsonResourceResponse([
            'user@example.com' => 'John',
            'admin@example.com' => 'Hank',
        ], [
            'data' => [
                'user@example.com' => 'John',
                'admin@example.com' => 'Hank',
            ],
        ]);
    }

    public function testItWillReturnAsAnArrayWhenStringKeysAreStripped()
    {
        $this->assertJsonResourceResponse([
            1 => 'John',
            2 => 'Hank',
            'foo' => new MissingValue(),
        ], ['data' => ['John', 'Hank']]);

        $this->assertJsonResourceResponse([
            1 => 'John',
            'foo' => new MissingValue(),
            3 => 'Hank',
        ], ['data' => ['John', 'Hank']]);

        $this->assertJsonResourceResponse([
            'foo' => new MissingValue(),
            2 => 'John',
            3 => 'Hank',
        ], ['data' => ['John', 'Hank']]);
    }

    public function testItStripsNumericKeys()
    {
        $this->assertJsonResourceResponse([
            0 => 'John',
            1 => 'Hank',
        ], ['data' => ['John', 'Hank']]);

        $this->assertJsonResourceResponse([
            0 => 'John',
            1 => 'Hank',
            3 => 'Bill',
        ], ['data' => ['John', 'Hank', 'Bill']]);

        $this->assertJsonResourceResponse([
            5 => 'John',
            6 => 'Hank',
        ], ['data' => ['John', 'Hank']]);
    }

    public function testItWontKeysIfAnyOfThemAreStrings()
    {
        $this->assertJsonResourceResponse([
            '5' => 'John',
            '6' => 'Hank',
            'a' => 'Bill',
        ], ['data' => ['5' => 'John', '6' => 'Hank', 'a' => 'Bill']]);

        $this->assertJsonResourceResponse([
            0 => 10,
            1 => 20,
            'total' => 30,
        ], ['data' => [0 => 10, 1 => 20, 'total' => 30]]);
    }

    private function assertJsonResourceResponse($data, $expectedJson)
    {
        $this->http(function () use ($data) {
            return (new JsonResource($data))->toResponse();
        })->assertJson($expectedJson);

        $this->http(function () use ($data) {
            return new JsonResource($data);
        })->assertJson($expectedJson);
    }
}
