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

namespace HyperfTest\Database;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Model\Relations\MorphTo;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class WhereHasMorphTest extends TestCase
{
    protected function setUp(): void
    {
        $container = ContainerStub::getContainer();
        $db = new Db($container);
        $resolver = $container->get(ConnectionResolverInterface::class);
        $container->shouldReceive('get')->with(Db::class)->andReturn($db);
        Register::setConnectionResolver($resolver);
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->dropSchema();
        Mockery::close();
        $reflection = new ReflectionClass(ApplicationContext::class);
        $reflection->setStaticPropertyValue('container', null);
    }

    public function testWhereHasMorph(): void
    {
        $comments = MorphComment::whereHasMorph('commentable', [MorphPost::class, Video::class], function (Builder $query) {
            $query->where('title', 'foo');
        })->orderBy('id')->get();

        $this->assertEquals([1, 4], $comments->pluck('id')->all());
    }

    public function testWhereHasMorphWithMorphMap()
    {
        Relation::morphMap(['posts' => MorphPost::class]);

        MorphComment::where('commentable_type', MorphPost::class)->update(['commentable_type' => 'posts']);

        try {
            $comments = MorphComment::whereHasMorph('commentable', [MorphPost::class, Video::class], function (Builder $query) {
                $query->where('title', 'foo');
            })->orderBy('id')->get();

            $this->assertEquals([1, 4], $comments->pluck('id')->all());
        } finally {
            Relation::morphMap([], false);
        }
    }

    public function testWhereHasMorphWithWildcard()
    {
        // Test newModelQuery() without global scopes.
        MorphComment::where('commentable_type', Video::class)->delete();

        $comments = MorphComment::withTrashed()
            ->whereHasMorph('commentable', '*', function (Builder $query) {
                $query->where('title', 'foo');
            })->orderBy('id')->get();

        $this->assertEquals([1, 4], $comments->pluck('id')->all());
    }

    public function testWhereHasMorphWithWildcardAndMorphMap()
    {
        Relation::morphMap(['posts' => MorphPost::class]);

        MorphComment::where('commentable_type', MorphPost::class)->update(['commentable_type' => 'posts']);

        try {
            $comments = MorphComment::whereHasMorph('commentable', '*', function (Builder $query) {
                $query->where('title', 'foo');
            })->orderBy('id')->get();

            $this->assertEquals([1, 4], $comments->pluck('id')->all());
        } finally {
            Relation::morphMap([], false);
        }
    }

    public function testWhereHasMorphWithWildcardAndOnlyNullMorphTypes()
    {
        MorphComment::whereNotNull('commentable_type')->delete();

        $comments = MorphComment::query()
            ->whereHasMorph('commentable', '*', function (Builder $query) {
                $query->where('title', 'foo');
            })
            ->orderBy('id')->get();

        $this->assertEmpty($comments->pluck('id')->all());
    }

    public function testWhereHasMorphWithRelationConstraint()
    {
        $comments = MorphComment::whereHasMorph('commentableWithConstraint', Video::class, function (Builder $query) {
            $query->where('title', 'like', 'ba%');
        })->orderBy('id')->get();

        $this->assertEquals([5], $comments->pluck('id')->all());
    }

    public function testWhereHasMorphWitDifferentConstraints()
    {
        $comments = MorphComment::whereHasMorph('commentable', [MorphPost::class, Video::class], function (Builder $query, $type) {
            if ($type === MorphPost::class) {
                $query->where('title', 'foo');
            }

            if ($type === Video::class) {
                $query->where('title', 'bar');
            }
        })->orderBy('id')->get();

        $this->assertEquals([1, 5], $comments->pluck('id')->all());
    }

    public function testWhereHasMorphWithOwnerKey()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_commentable_type_commentable_id_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->string('commentable_id')->nullable()->change();
        });

        MorphPost::where('id', 1)->update(['slug' => 'foo']);

        MorphComment::where('id', 1)->update(['commentable_id' => 'foo']);

        $comments = MorphComment::whereHasMorph('commentableWithOwnerKey', MorphPost::class, function (Builder $query) {
            $query->where('title', 'foo');
        })->orderBy('id')->get();

        $this->assertEquals([1], $comments->pluck('id')->all());
    }

    public function testHasMorph()
    {
        $comments = MorphComment::hasMorph('commentable', MorphPost::class)->orderBy('id')->get();

        $this->assertEquals([1, 2], $comments->pluck('id')->all());
    }

    public function testOrHasMorph()
    {
        $comments = MorphComment::where('id', 1)->orHasMorph('commentable', Video::class)->orderBy('id')->get();

        $this->assertEquals([1, 4, 5, 6], $comments->pluck('id')->all());
    }

    public function testDoesntHaveMorph()
    {
        $comments = MorphComment::doesntHaveMorph('commentable', MorphPost::class)->orderBy('id')->get();

        $this->assertEquals([3], $comments->pluck('id')->all());
    }

    public function testOrDoesntHaveMorph()
    {
        $comments = MorphComment::where('id', 1)->orDoesntHaveMorph('commentable', MorphPost::class)->orderBy('id')->get();

        $this->assertEquals([1, 3], $comments->pluck('id')->all());
    }

    public function testOrWhereHasMorph()
    {
        $comments = MorphComment::where('id', 1)
            ->orWhereHasMorph('commentable', Video::class, function (Builder $query) {
                $query->where('title', 'foo');
            })->orderBy('id')->get();

        $this->assertEquals([1, 4], $comments->pluck('id')->all());
    }

    public function testOrWhereHasMorphWithWildcardAndOnlyNullMorphTypes()
    {
        MorphComment::whereNotNull('commentable_type')->forceDelete();

        $comments = MorphComment::where('id', 7)
            ->orWhereHasMorph('commentable', '*', function (Builder $query) {
                $query->where('title', 'foo');
            })->orderBy('id')->get();

        $this->assertEquals([7], $comments->pluck('id')->all());
    }

    public function testWhereDoesntHaveMorph()
    {
        $comments = MorphComment::whereDoesntHaveMorph('commentable', MorphPost::class, function (Builder $query) {
            $query->where('title', 'foo');
        })->orderBy('id')->get();

        $this->assertEquals([2, 3], $comments->pluck('id')->all());
    }

    public function testWhereDoesntHaveMorphWithWildcardAndOnlyNullMorphTypes()
    {
        MorphComment::whereNotNull('commentable_type')->forceDelete();

        $comments = MorphComment::whereDoesntHaveMorph('commentable', [], function (Builder $query) {
            $query->where('title', 'foo');
        })->orderBy('id')->get();

        $this->assertEquals([7, 8], $comments->pluck('id')->all());
    }

    public function testOrWhereDoesntHaveMorph()
    {
        $comments = MorphComment::where('id', 1)
            ->orWhereDoesntHaveMorph('commentable', MorphPost::class, function (Builder $query) {
                $query->where('title', 'foo');
            })->orderBy('id')->get();

        $this->assertEquals([1, 2, 3], $comments->pluck('id')->all());
    }

    public function testModelScopesAreAccessible()
    {
        $comments = MorphComment::whereHasMorph('commentable', [MorphPost::class, Video::class], function (Builder $query) {
            $query->someSharedModelScope();
        })->orderBy('id')->get();

        $this->assertEquals([1, 4], $comments->pluck('id')->all());
    }

    protected function connection($connection = 'default'): Connection
    {
        return Register::getConnectionResolver()->connection($connection);
    }

    protected function schema($connection = 'default'): Builder
    {
        return $this->connection($connection)->getSchemaBuilder();
    }

    protected function createSchema(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->softDeletes();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableMorphs('commentable');
            $table->softDeletes();
        });

        $models = [];

        $models[] = MorphPost::create(['title' => 'foo']);
        $models[] = MorphPost::create(['title' => 'bar']);
        $models[] = MorphPost::create(['title' => 'baz']);
        end($models)->delete();

        $models[] = Video::create(['title' => 'foo']);
        $models[] = Video::create(['title' => 'bar']);
        $models[] = Video::create(['title' => 'baz']);
        $models[] = null; // deleted
        $models[] = null; // deleted

        foreach ($models as $model) {
            (new MorphComment())->commentable()->associate($model)->save();
        }
    }

    protected function dropSchema(): void
    {
        Schema::drop('posts');
        Schema::drop('videos');
        Schema::drop('comments');
    }
}

class MorphComment extends Model
{
    use SoftDeletes;

    public bool $timestamps = false;

    protected ?string $table = 'comments';

    protected array $guarded = [];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function commentableWithConstraint(): MorphTo
    {
        return $this->morphTo('commentable')->where('title', 'bar');
    }

    public function commentableWithOwnerKey(): MorphTo
    {
        return $this->morphTo('commentable', null, null, 'slug');
    }
}

class MorphPost extends Model
{
    use SoftDeletes;

    public bool $timestamps = false;

    protected ?string $table = 'posts';

    protected array $guarded = [];

    public function scopeSomeSharedModelScope($query): void
    {
        $query->where('title', '=', 'foo');
    }
}

class Video extends Model
{
    public bool $timestamps = false;

    protected array $guarded = [];

    public function scopeSomeSharedModelScope($query): void
    {
        $query->where('title', '=', 'foo');
    }
}
