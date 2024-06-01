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

use DateTimeInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Model\Relations\Pivot;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\Database\Model\SoftDeletingScope;
use Hyperf\Database\Schema\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Container;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class DatabaseIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();
        $container->shouldReceive('get')->with('db.connector.mysql')->andReturn(new MySqlConnector());
        $connector = new ConnectionFactory($container);

        $dbConfig = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'hyperf',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ];

        $db2Config = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'hyperf2',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ];

        $connection = $connector->make($dbConfig);
        $connection2 = $connector->make($db2Config);

        $resolver = new ConnectionResolver(['default' => $connection, 'test' => $connection2]);

        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver);
        $container = ContainerStub::getContainer();
        $db = new Db($container);

        $container->shouldReceive('get')->with(Db::class)->andReturn($db);
        $connectionResolverInterface = $container->get(ConnectionResolverInterface::class);
        Register::setConnectionResolver($connectionResolverInterface);
    }

    protected function down()
    {
        foreach (['default', 'second_connection'] as $connection) {
            $this->schema($connection)->drop('users');
            $this->schema($connection)->drop('friends');
            $this->schema($connection)->drop('posts');
            $this->schema($connection)->drop('friend_levels');
            $this->schema($connection)->drop('photos');
        }
        Register::unsetConnectionResolver();
    }

    public function testUpdateOrCreateOnDifferentConnection(): void
    {
        $this->createSchema();
        ModelTestUser::create(['email' => 'taylorotwell@gmail.com']);

        ModelTestUser::on('second_connection')->updateOrCreate(
            ['email' => 'taylorotwell@gmail.com'],
            ['name' => 'Taylor Otwell']
        );

        ModelTestUser::on('second_connection')->updateOrCreate(
            ['email' => 'themsaid@gmail.com'],
            ['name' => 'Mohamed Said']
        );

        $this->assertEquals(1, ModelTestUser::count());
        $this->assertEquals(2, ModelTestUser::on('second_connection')->count());
        $this->down();
    }

    public function testCheckAndCreateMethodsOnMultiConnections(): void
    {
        $this->createSchema();
        ModelTestUser::create(['id' => 1, 'email' => 'taylorotwell@gmail.com']);
        ModelTestUser::on('second_connection')->find(
            ModelTestUser::on('second_connection')->insert(['id' => 2, 'email' => 'themsaid@gmail.com'])
        );

        $user1 = ModelTestUser::on('second_connection')->findOrNew(1);
        $user2 = ModelTestUser::on('second_connection')->findOrNew(2);
        $this->assertFalse($user1->exists);
        $this->assertTrue($user2->exists);
        $this->assertSame('second_connection', $user1->getConnectionName());
        $this->assertSame('second_connection', $user2->getConnectionName());

        $user1 = ModelTestUser::on('second_connection')->firstOrNew(['email' => 'taylorotwell@gmail.com']);
        $user2 = ModelTestUser::on('second_connection')->firstOrNew(['email' => 'themsaid@gmail.com']);
        $this->assertFalse($user1->exists);
        $this->assertTrue($user2->exists);
        $this->assertSame('second_connection', $user1->getConnectionName());
        $this->assertSame('second_connection', $user2->getConnectionName());

        $this->assertEquals(1, ModelTestUser::on('second_connection')->count());
        $user1 = ModelTestUser::on('second_connection')->firstOrCreate(['email' => 'taylorotwell@gmail.com']);
        $user2 = ModelTestUser::on('second_connection')->firstOrCreate(['email' => 'themsaid@gmail.com']);
        $this->assertSame('second_connection', $user1->getConnectionName());
        $this->assertSame('second_connection', $user2->getConnectionName());
        $this->assertEquals(2, ModelTestUser::on('second_connection')->count());
        $this->down();
    }

    protected function createSchema(): void
    {
        $this->schema('default')->create('test_orders', function ($table) {
            $table->increments('id');
            $table->string('item_type');
            $table->integer('item_id');
            $table->timestamps();
        });

        $this->schema('default')->create('with_json', function ($table) {
            $table->increments('id');
            $table->text('json')->default(json_encode([]));
        });

        $this->schema('second_connection')->create('test_items', function ($table) {
            $table->increments('id');
            $table->timestamps();
        });

        $this->schema('default')->create('users_with_space_in_column_name', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email address');
            $table->timestamps();
        });

        foreach (['default', 'second_connection'] as $connection) {
            $this->schema($connection)->create('users', function ($table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('email');
                $table->timestamp('birthday', 6)->nullable();
                $table->timestamps();
            });

            $this->schema($connection)->create('unique_users', function ($table) {
                $table->increments('id');
                $table->string('name')->nullable();
                // Unique constraint will be applied only for non-null values
                $table->string('screen_name')->nullable()->unique();
                $table->string('email')->unique();
                $table->timestamp('birthday', 6)->nullable();
                $table->timestamps();
            });

            $this->schema($connection)->create('friends', function ($table) {
                $table->integer('user_id');
                $table->integer('friend_id');
                $table->integer('friend_level_id')->nullable();
            });

            $this->schema($connection)->create('posts', function ($table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('parent_id')->nullable();
                $table->string('name');
                $table->timestamps();
            });

            $this->schema($connection)->create('comments', function ($table) {
                $table->increments('id');
                $table->integer('post_id');
                $table->string('content');
                $table->timestamps();
            });

            $this->schema($connection)->create('friend_levels', function ($table) {
                $table->increments('id');
                $table->string('level');
                $table->timestamps();
            });

            $this->schema($connection)->create('photos', function ($table) {
                $table->increments('id');
                $table->morphs('imageable');
                $table->string('name');
                $table->timestamps();
            });

            $this->schema($connection)->create('soft_deleted_users', function ($table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('email');
                $table->timestamps();
                $table->softDeletes();
            });

            $this->schema($connection)->create('tags', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });

            $this->schema($connection)->create('taggables', function ($table) {
                $table->integer('tag_id');
                $table->morphs('taggable');
                $table->string('taxonomy')->nullable();
            });
        }

        $this->schema($connection)->create('non_incrementing_users', function ($table) {
            $table->string('name')->nullable();
        });
        $this->down();
    }

    protected function connection($connection = 'default'): Connection
    {
        return Register::getConnectionResolver()->connection($connection);
    }

    protected function schema($connection = 'default'): Builder
    {
        return $this->connection($connection)->getSchemaBuilder();
    }
}
class ModelTestUser extends Model
{
    protected ?string $table = 'users';

    protected array $casts = ['birthday' => 'datetime'];

    protected array $guarded = [];

    public function friends()
    {
        return $this->belongsToMany(self::class, 'friends', 'user_id', 'friend_id');
    }

    public function friendsOne()
    {
        return $this->belongsToMany(self::class, 'friends', 'user_id', 'friend_id')->wherePivot('user_id', 1);
    }

    public function friendsTwo()
    {
        return $this->belongsToMany(self::class, 'friends', 'user_id', 'friend_id')->wherePivot('user_id', 2);
    }

    public function posts()
    {
        return $this->hasMany(ModelTestPost::class, 'user_id');
    }

    public function post()
    {
        return $this->hasOne(ModelTestPost::class, 'user_id');
    }

    public function photos()
    {
        return $this->morphMany(ModelTestPhoto::class, 'imageable');
    }

    public function postWithPhotos()
    {
        return $this->post()->join('photo', function ($join) {
            $join->on('photo.imageable_id', 'post.id');
            $join->where('photo.imageable_type', 'ModelTestPost');
        });
    }
}

class ModelTestUserWithCustomFriendPivot extends ModelTestUser
{
    public function friends()
    {
        return $this->belongsToMany(ModelTestUser::class, 'friends', 'user_id', 'friend_id')
            ->using(ModelTestFriendPivot::class)->withPivot('user_id', 'friend_id', 'friend_level_id');
    }
}

class ModelTestUserWithSpaceInColumnName extends ModelTestUser
{
    protected ?string $table = 'users_with_space_in_column_name';
}

class ModelTestNonIncrementing extends Model
{
    public bool $incrementing = false;

    public bool $timestamps = false;

    protected ?string $table = 'non_incrementing_users';

    protected array $guarded = [];
}

class ModelTestNonIncrementingSecond extends ModelTestNonIncrementing
{
    protected ?string $connection = 'second_connection';
}

class ModelTestUserWithGlobalScope extends ModelTestUser
{
    public function boot(): void
    {
        parent::boot();

        static::addGlobalScope(function ($builder) {
            $builder->with('posts');
        });
    }
}

class ModelTestUserWithOmittingGlobalScope extends ModelTestUser
{
    public function boot(): void
    {
        parent::boot();

        static::addGlobalScope(function ($builder) {
            $builder->where('email', '!=', 'taylorotwell@gmail.com');
        });
    }
}

class ModelTestUserWithGlobalScopeRemovingOtherScope extends Model
{
    use SoftDeletes;

    protected ?string $table = 'soft_deleted_users';

    protected array $guarded = [];

    public function boot(): void
    {
        static::addGlobalScope(function ($builder) {
            $builder->withoutGlobalScope(SoftDeletingScope::class);
        });

        parent::boot();
    }
}

class ModelTestUniqueUser extends Model
{
    protected ?string $table = 'unique_users';

    protected array $casts = ['birthday' => 'datetime'];

    protected array $guarded = [];
}

class ModelTestPost extends Model
{
    protected ?string $table = 'posts';

    protected array $guarded = [];

    public function user()
    {
        return $this->belongsTo(ModelTestUser::class, 'user_id');
    }

    public function photos()
    {
        return $this->morphMany(ModelTestPhoto::class, 'imageable');
    }

    public function childPosts()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parentPost()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function tags()
    {
        return $this->morphToMany(ModelTestTag::class, 'taggable', null, null, 'tag_id')->withPivot('taxonomy');
    }
}

class ModelTestTag extends Model
{
    protected ?string $table = 'tags';

    protected array $guarded = [];
}

class ModelTestFriendLevel extends Model
{
    protected ?string $table = 'friend_levels';

    protected array $guarded = [];
}

class ModelTestPhoto extends Model
{
    protected ?string $table = 'photos';

    protected array $guarded = [];

    public function imageable()
    {
        return $this->morphTo();
    }
}

class ModelTestUserWithStringCastId extends ModelTestUser
{
    protected array $casts = [
        'id' => 'string',
    ];
}

class ModelTestUserWithCustomDateSerialization extends ModelTestUser
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-y');
    }
}

class ModelTestOrder extends Model
{
    protected array $guarded = [];

    protected ?string $table = 'test_orders';

    protected array $with = ['item'];

    public function item()
    {
        return $this->morphTo();
    }
}

class ModelTestItem extends Model
{
    protected array $guarded = [];

    protected ?string $table = 'test_items';

    protected ?string $connection = 'second_connection';
}

class ModelTestWithJSON extends Model
{
    public bool $timestamps = false;

    protected array $guarded = [];

    protected ?string $table = 'with_json';

    protected array $casts = [
        'json' => 'array',
    ];
}

class ModelTestFriendPivot extends Pivot
{
    public bool $timestamps = false;

    protected ?string $table = 'friends';

    protected array $guarded = [];

    public function user()
    {
        return $this->belongsTo(ModelTestUser::class);
    }

    public function friend()
    {
        return $this->belongsTo(ModelTestUser::class);
    }

    public function level()
    {
        return $this->belongsTo(ModelTestFriendLevel::class, 'friend_level_id');
    }
}

class ModelTouchingUser extends Model
{
    protected ?string $table = 'users';

    protected array $guarded = [];
}

class ModelTouchingPost extends Model
{
    protected ?string $table = 'posts';

    protected array $guarded = [];

    protected array $touches = [
        'user',
    ];

    public function user()
    {
        return $this->belongsTo(ModelTouchingUser::class, 'user_id');
    }
}

class ModelTouchingComment extends Model
{
    protected ?string $table = 'comments';

    protected array $guarded = [];

    protected array $touches = [
        'post',
    ];

    public function post()
    {
        return $this->belongsTo(ModelTouchingPost::class, 'post_id');
    }
}
