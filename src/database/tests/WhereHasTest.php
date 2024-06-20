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
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Builder as ModelBuilder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Query\Builder as QueryBuilder;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function Hyperf\Tappable\tap;

/**
 * @internal
 * @coversNothing
 */
class WhereHasTest extends TestCase
{
    protected function setUp(): void
    {
        $container = ContainerStub::getContainer();
        $db = new Db($container);
        $container->shouldReceive('get')->with(Db::class)->andReturn($db);
        $connectionResolverInterface = $container->get(ConnectionResolverInterface::class);
        Register::setConnectionResolver($connectionResolverInterface);
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('texts');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
        Mockery::close();
        $reflection = new ReflectionClass(ApplicationContext::class);
        $reflection->setStaticPropertyValue('container', null);
    }

    public function createSchema()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->boolean('public');
        });

        Schema::create('texts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->text('content');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('commentable_type');
            $table->integer('commentable_id');
        });

        $user = User::create();
        $post = tap((new Post(['public' => true]))->user()->associate($user))->save();
        (new Comment())->commentable()->associate($post)->save();
        (new Text(['content' => 'test']))->post()->associate($post)->save();

        $user = User::create();
        $post = tap((new Post(['public' => false]))->user()->associate($user))->save();
        (new Comment())->commentable()->associate($post)->save();
        (new Text(['content' => 'test2']))->post()->associate($post)->save();
    }

    public static function dataProviderWhereRelationCallback()
    {
        $callbackArray = function ($value) {
            $callbackEloquent = function (ModelBuilder $builder) use ($value) {
                $builder->selectRaw('id')->where('public', $value);
            };

            $callbackQuery = function (QueryBuilder $builder) use ($value) {
                $hasMany = (new User())->posts();

                $builder->from('posts')->addSelect(['*'])->whereColumn(
                    $hasMany->getQualifiedParentKeyName(),
                    '=',
                    $hasMany->getQualifiedForeignKeyName()
                );

                $builder->selectRaw('id')->where('public', $value);
            };

            return [$callbackEloquent, $callbackQuery];
        };

        return [
            'Find user with post.public = true' => $callbackArray(true),
            'Find user with post.public = false' => $callbackArray(false),
        ];
    }

    public function testWhereRelation()
    {
        $users = User::whereRelation('posts', 'public', true)->get();

        $this->assertEquals([1], $users->pluck('id')->all());
    }

    public function testOrWhereRelation()
    {
        $users = User::whereRelation('posts', 'public', true)->orWhereRelation('posts', 'public', false)->get();

        $this->assertEquals([1, 2], $users->pluck('id')->all());
    }

    public function testNestedWhereRelation()
    {
        $texts = User::whereRelation('posts.texts', 'content', 'test')->get();

        $this->assertEquals([1], $texts->pluck('id')->all());
    }

    public function testNestedOrWhereRelation()
    {
        $texts = User::whereRelation('posts.texts', 'content', 'test')->orWhereRelation('posts.texts', 'content', 'test2')->get();

        $this->assertEquals([1, 2], $texts->pluck('id')->all());
    }

    public function testWhereMorphRelation()
    {
        $comments = Comment::whereMorphRelation('commentable', '*', 'public', true)->get();

        $this->assertEquals([1], $comments->pluck('id')->all());
    }

    public function testOrWhereMorphRelation()
    {
        $comments = Comment::whereMorphRelation('commentable', '*', 'public', true)
            ->orWhereMorphRelation('commentable', '*', 'public', false)
            ->get();

        $this->assertEquals([1, 2], $comments->pluck('id')->all());
    }

    /**
     * Check that the 'whereRelation' callback function works.
     *
     * @dataProvider dataProviderWhereRelationCallback
     * @param mixed $callbackEloquent
     * @param mixed $callbackQuery
     */
    public function testWhereRelationCallback($callbackEloquent, $callbackQuery)
    {
        $userWhereRelation = User::whereRelation('posts', $callbackEloquent);
        $userWhereHas = User::whereHas('posts', $callbackEloquent);
        $query = Db::table('users')->whereExists($callbackQuery);

        $this->assertEquals($userWhereRelation->getQuery()->toSql(), $query->toSql());
        $this->assertEquals($userWhereRelation->getQuery()->toSql(), $userWhereHas->toSql());
        $this->assertEquals($userWhereHas->getQuery()->toSql(), $query->toSql());

        $this->assertEquals($userWhereRelation->first()->id, $query->first()->id);
        $this->assertEquals($userWhereRelation->first()->id, $userWhereHas->first()->id);
        $this->assertEquals($userWhereHas->first()->id, $query->first()->id);
    }

    /**
     * Check that the 'orWhereRelation' callback function works.
     *
     * @dataProvider dataProviderWhereRelationCallback
     * @param mixed $callbackEloquent
     * @param mixed $callbackQuery
     */
    public function testOrWhereRelationCallback($callbackEloquent, $callbackQuery): void
    {
        $userOrWhereRelation = User::orWhereRelation('posts', $callbackEloquent);
        $userOrWhereHas = User::orWhereHas('posts', $callbackEloquent);
        $query = Db::table('users')->orWhereExists($callbackQuery);

        $this->assertEquals($userOrWhereRelation->getQuery()->toSql(), $query->toSql());
        $this->assertEquals($userOrWhereRelation->getQuery()->toSql(), $userOrWhereHas->toSql());
        $this->assertEquals($userOrWhereHas->getQuery()->toSql(), $query->toSql());

        $this->assertEquals($userOrWhereRelation->first()->id, $query->first()->id);
        $this->assertEquals($userOrWhereRelation->first()->id, $userOrWhereHas->first()->id);
        $this->assertEquals($userOrWhereHas->first()->id, $query->first()->id);
    }
}

class Comment extends Model
{
    public bool $timestamps = false;

    public function commentable()
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    public bool $timestamps = false;

    protected array $guarded = [];

    protected array $withCount = ['comments'];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function texts()
    {
        return $this->hasMany(Text::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class Text extends Model
{
    public bool $timestamps = false;

    protected array $guarded = [];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

class User extends Model
{
    public bool $timestamps = false;

    protected ?string $table = 'users';

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
