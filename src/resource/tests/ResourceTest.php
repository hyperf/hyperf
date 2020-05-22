<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Resource;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Di\ClosureDefinitionCollector;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Resource\ConditionallyLoadsAttributes;
use Hyperf\Resource\Json\JsonResource;
use Hyperf\Resource\Json\ResourceCollection;
use Hyperf\Resource\MergeValue;
use Hyperf\Resource\MissingValue;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use HyperfTest\HttpServer\Stub\CoreMiddlewareStub;
use JsonSerializable;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
class ResourceTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testResourcesMayBeConvertedToJson()
    {
        $response = $this->getResponse(function () {
            return new PostResource(new Post([
                'id' => 5,
                'title' => 'Test Title',
                'abstract' => 'Test abstract',
            ]));
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'title' => 'Test Title',
                'custom' => true,
            ],
        ], $response->getBody()->getContents());
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
        $response = $this->getResponse(function () {
            return ObjectResource::make(
                (object) ['first_name' => 'Bob', 'age' => 40]
            );
        });

        $this->assertJsonByArray([
            'data' => [
                'name' => 'Bob',
                'age' => 40,
            ],
        ], $response->getBody()->getContents());
    }

    public function testArraysWithObjectsMayBeConvertedToJson()
    {
        $response = $this->getResponse(function () {
            $objects = [
                (object) ['first_name' => 'Bob', 'age' => 40],
                (object) ['first_name' => 'Jack', 'age' => 25],
            ];

            return ObjectResource::collection($objects);
        });
        $this->assertJsonByArray([
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
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayHaveNoWrap()
    {
        $response = $this->getResponse(function () {
            return new PostResourceWithoutWrap(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]));
        });
        $this->assertJsonByArray([
            'id' => 5,
            'title' => 'Test Title',
            'custom' => true,
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayHaveOptionalValues()
    {
        $response = $this->getResponse(function () {
            return new PostResourceWithOptionalData(new Post([
                'id' => 5,
            ]));
        });

        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'second' => 'value',
                'third' => 'value',
                'fourth' => 'default',
                'fifth' => 'default',
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayHaveOptionalMerges()
    {
        $response = $this->getResponse(function () {
            return new PostResourceWithOptionalMerging(new Post([
                'id' => 5,
            ]));
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'second' => 'value',
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayHaveOptionalRelationships()
    {
        $response = $this->getResponse(function () {
            return new PostResourceWithOptionalRelationship(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]));
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayLoadOptionalRelationships()
    {
        $response = $this->getResponse(function () {
            $post = new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]);

            $post->setRelation('author', new Author(['name' => 'jrrmartin']));

            return new PostResourceWithOptionalRelationship($post);
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'author' => ['name' => 'jrrmartin'],
                'author_name' => 'jrrmartin',
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayShowsNullForLoadedRelationshipWithValueNull()
    {
        $response = $this->getResponse(function () {
            $post = new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]);

            $post->setRelation('author', null);

            return new PostResourceWithOptionalRelationship($post);
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'author' => null,
                'author_name' => null,
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayHaveOptionalRelationshipsWithDefaultValues()
    {
        $response = $this->getResponse(function () {
            return new AuthorResourceWithOptionalRelationship(new Author([
                'name' => 'jrrmartin',
            ]));
        });
        $this->assertJsonByArray([
            'data' => [
                'name' => 'jrrmartin',
                'posts_count' => 'not loaded',
                'latest_post_title' => 'not loaded',
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayHaveOptionalPivotRelationships()
    {
        $response = $this->getResponse(function () {
            $post = new Post(['id' => 5]);
            $post->setRelation('pivot', new Subscription());

            return new PostResourceWithOptionalPivotRelationship($post);
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'subscription' => [
                    'foo' => 'bar',
                ],
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayHaveOptionalPivotRelationshipsWithCustomAccessor()
    {
        $response = $this->getResponse(function () {
            $post = new Post(['id' => 5]);
            $post->setRelation('accessor', new Subscription());

            return new PostResourceWithOptionalPivotRelationship($post);
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'custom_subscription' => [
                    'foo' => 'bar',
                ],
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayBeSerializable()
    {
        $response = $this->getResponse(function () {
            return new SerializablePostResource(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]));
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
            ],
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayCustomizeExtraData()
    {
        $response = $this->getResponse(function () {
            return new PostResourceWithExtraData(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]));
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'title' => 'Test Title',
                'custom' => true,
            ],
            'foo' => 'bar',
        ], $response->getBody()->getContents());
    }

    public function testResourcesMayCustomizeExtraDataWhenBuildingResponse()
    {
        $response = $this->getResponse(function () {
            return (new PostResourceWithExtraData(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ])))->additional(['baz' => 'qux']);
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'title' => 'Test Title',
                'custom' => true,
            ],
            'foo' => 'bar',
            'baz' => 'qux',
        ], $response->getBody()->getContents());
    }

    public function testCollectionsAreNotDoubledWrapped()
    {
        $response = $this->getResponse(function () {
            return new PostCollectionResource(collect([new Post([
                'id' => 5,
                'title' => 'Test Title',
            ])]));
        });
        $this->assertJsonByArray([
            'data' => [
                [
                    'id' => 5,
                    'title' => 'Test Title',
                    'custom' => true,
                ],
            ],
        ], $response->getBody()->getContents());
    }

    public function testPaginatorsReceiveLinks()
    {
        $response = $this->getResponse(function () {
            $paginator = new LengthAwarePaginator(
                collect([new Post(['id' => 5, 'title' => 'Test Title'])]),
                10,
                15,
                1
            );

            return new PostCollectionResource($paginator);
        });
        $this->assertJsonByArray([
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
        ], $response->getBody()->getContents());
    }

    public function testToJsonMayBeLeftOffOfSingleResource()
    {
        $response = $this->getResponse(function () {
            return new ReallyEmptyPostResource(new Post([
                'id' => 5,
                'title' => 'Test Title',
            ]));
        });
        $this->assertJsonByArray([
            'data' => [
                'id' => 5,
                'title' => 'Test Title',
            ],
        ], $response->getBody()->getContents());
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

        $response = $this->getResponse(function () use ($data) {
            return new ResourceWithPreservedKeys($data);
        });

        $this->assertJsonByArray(['data' => $data], $response->getBody()->getContents());
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

        $response = $this->getResponse(function () use ($data) {
            return ResourceWithPreservedKeys::collection($data);
        });

        $this->assertJsonByArray(['data' => $data->toArray()], $response->getBody()->getContents());
    }

    public function testLeadingMergeKeyedValueIsMergedCorrectly()
    {
        $filter = new class() {
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
        $filter = new class() {
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
        $filter = new class() {
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
        $filter = new class() {
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
        $filter = new class() {
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
        $filter = new class() {
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
        $filter = new class() {
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
        $filter = new class() {
            use ConditionallyLoadsAttributes;

            public function work()
            {
                return $this->filter([
                    $this->mergeWhen(false, ['First', 'Second']),
                    'Taylor',
                    'Mohamed',
                    $this->mergeWhen(false, ['Adam', 'Matt']),
                    'Jeffrey',
                    $this->mergeWhen(false, (['Abigail', 'Lydia'])),
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
        $filter = new class() {
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

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(DispatcherFactory::class)->andReturn(new DispatcherFactory());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)
            ->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);
        $container->shouldReceive('get')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(new ClosureDefinitionCollector());
        $container->shouldReceive('get')->with(NormalizerInterface::class)
            ->andReturn(new SimpleNormalizer());
        return $container;
    }

    protected function getResponse($except)
    {
        $middleware = new CoreMiddlewareStub($container = $this->getContainer(), 'http');
        $reflectionMethod = new ReflectionMethod(CoreMiddleware::class, 'transferToResponse');
        $reflectionMethod->setAccessible(true);
        $request = Mockery::mock(ServerRequestInterface::class);
        return $reflectionMethod->invoke($middleware, $except(), $request);
    }

    protected function assertJsonByArray(array $expected, $actual, string $message = '')
    {
        $this->assertSame(json_encode($expected), $actual, $message);
    }

    private function assertJsonResourceResponse($data, $expectedJson)
    {
        $response = $this->getResponse(function () use ($data) {
            return new JsonResource($data);
        });

        $this->assertJsonByArray($expectedJson, $response->getBody()->getContents());
    }
}

class Author extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}

class AuthorResource extends JsonResource
{
    public function toArray()
    {
        return ['name' => $this->name];
    }
}

class PostResource extends JsonResource
{
    public function toArray()
    {
        return ['id' => $this->id, 'title' => $this->title, 'custom' => true];
    }
}

class AuthorResourceWithOptionalRelationship extends PostResource
{
    public function toArray()
    {
        return [
            'name' => $this->name,
            'posts_count' => $this->whenLoaded('posts', function () {
                return $this->posts->count() . ' posts';
            }, function () {
                return 'not loaded';
            }),
            'latest_post_title' => $this->whenLoaded('posts', function () {
                $post = $this->posts->first();
                return $post ? ($post->title ?: 'no posts yet') : 'no posts yet';
            }, 'not loaded'),
        ];
    }
}

class CommentCollection extends ResourceCollection
{
}

class Post extends Model
{
    protected $guarded = [];
}

class ObjectResource extends JsonResource
{
    public function toArray()
    {
        return [
            'name' => $this->first_name,
            'age' => $this->age,
        ];
    }
}

class PostResourceWithoutWrap extends PostResource
{
    public $wrap;
}

class PostResourceWithOptionalData extends JsonResource
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            'first' => $this->when(false, 'value'),
            'second' => $this->when(true, 'value'),
            'third' => $this->when(true, function () {
                return 'value';
            }),
            'fourth' => $this->when(false, 'value', 'default'),
            'fifth' => $this->when(false, 'value', function () {
                return 'default';
            }),
        ];
    }
}

class PostResourceWithOptionalMerging extends JsonResource
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            $this->mergeWhen(false, ['first' => 'value']),
            $this->mergeWhen(true, ['second' => 'value']),
        ];
    }
}

class ResourceWithPreservedKeys extends PostResource
{
    protected $preserveKeys = true;

    public function toArray()
    {
        return $this->resource;
    }
}

class PostCollectionResource extends ResourceCollection
{
    public $collects = PostResource::class;

    public function toArray()
    {
        return ['data' => $this->collection];
    }
}

class EmptyPostCollectionResource extends ResourceCollection
{
    public $collects = PostResource::class;
}

class ReallyEmptyPostResource extends JsonResource
{
}

class PostResourceWithExtraData extends PostResource
{
    public function with()
    {
        return ['foo' => 'bar'];
    }
}

class JsonSerializableResource implements JsonSerializable
{
    public $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->resource->id,
        ];
    }
}

class SerializablePostResource extends JsonResource
{
    public function toArray()
    {
        return new JsonSerializableResource($this);
    }
}

class PostResourceWithOptionalRelationship extends PostResource
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            'comments' => new CommentCollection($this->whenLoaded('comments')),
            'author' => new AuthorResource($this->whenLoaded('author')),
            'author_name' => $this->whenLoaded('author', function () {
                return $this->author->name;
            }),
        ];
    }
}

class Subscription
{
}

class PostResourceWithOptionalPivotRelationship extends PostResource
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            'subscription' => $this->whenPivotLoaded(Subscription::class, function () {
                return [
                    'foo' => 'bar',
                ];
            }),
            'custom_subscription' => $this->whenPivotLoadedAs('accessor', Subscription::class, function () {
                return [
                    'foo' => 'bar',
                ];
            }),
        ];
    }
}
