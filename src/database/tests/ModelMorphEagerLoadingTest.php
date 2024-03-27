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

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\MorphTo;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Engine\Channel;
use HyperfTest\Database\Stubs\ContainerStub;
use HyperfTest\Database\Stubs\Model\Book;
use HyperfTest\Database\Stubs\Model\Image;
use HyperfTest\Database\Stubs\Model\User;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ModelMorphEagerLoadingTest extends TestCase
{
    /**
     * @var array
     */
    protected $channel;

    protected function setUp(): void
    {
        Relation::morphMap([
            'user' => User::class,
            'book' => Book::class,
        ]);
        $this->channel = new Channel(999);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Relation::$morphMap = [];
        $this->channel->close();
    }

    public function testMorphOne()
    {
        $this->getContainer();
        $user = User::query()->find(1);
        $image = $user->image;
        $this->assertSame(1, $image->id);

        $sqls = [
            ['select * from `user` where `user`.`id` = ? limit 1', [1]],
            ['select * from `images` where `images`.`imageable_id` = ? and `images`.`imageable_id` is not null and `images`.`imageable_type` = ? limit 1', [1, 'user']],
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame([$event->sql, $event->bindings], array_shift($sqls));
            }
        }
    }

    public function testMorphWith()
    {
        $this->getContainer();
        $images = Image::query()->with([
            'imageable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Book::class => ['user'],
                ]);
            },
        ])->get();

        $this->assertInstanceOf(Book::class, $images[2]->imageable);
        $this->assertSame(1, $images[2]->imageable->id);
        $this->assertInstanceOf(User::class, $images[2]->imageable->user);
        $this->assertSame(1, $images[2]->imageable->user->id);

        $sqls = [
            'select * from `images`',
            'select * from `user` where `user`.`id` in (1, 2)',
            'select * from `book` where `book`.`id` in (1, 2, 3)',
            'select * from `user` where `user`.`id` in (1, 2)',
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame($event->sql, array_shift($sqls));
            }
        }
    }

    public function testMorphAssociationEmpty()
    {
        $this->getContainer();
        $images = Image::query()->whereHasMorph(
            'imageable',
            ['*'],
            function (Builder $query) {
                $query->where('imageable_id', 1);
            }
        )->get();

        $this->assertSame(2, $images->count());
    }

    public function testWhereHasMorph()
    {
        $this->getContainer();
        $images = Image::query()->whereHasMorph(
            'imageable',
            [
                User::class,
                Book::class,
            ],
            function (Builder $query) {
                $query->where('imageable_id', 1);
            }
        )->get();

        $this->assertSame(2, $images->count());
        $this->assertSame('user', $images[0]->imageable_type);
        $this->assertSame('book', $images[1]->imageable_type);

        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame('select * from `images` where ((`imageable_type` = ? and exists (select * from `user` where `images`.`imageable_id` = `user`.`id` and `imageable_id` = ?)) or (`imageable_type` = ? and exists (select * from `book` where `images`.`imageable_id` = `book`.`id` and `imageable_id` = ?)))', $event->sql);
            }
        }
    }

    public function testOrWhereHasMorph()
    {
        $this->getContainer();
        $images = Image::query()
            ->whereHasMorph(
                'imageable',
                [
                    User::class,
                ],
                function (Builder $query) {
                    $query->where('id', '=', 1);
                }
            )
            ->orWhereHasMorph(
                'imageable',
                [
                    Book::class,
                ],
                function (Builder $query) {
                    $query->where('id', '=', 1);
                }
            )->get();
        $this->assertSame(1, $images[0]->imageable->id);
        $this->assertSame(1, $images[1]->imageable->id);
        $sqls = [
            ['select * from `images` where ((`imageable_type` = ? and exists (select * from `user` where `images`.`imageable_id` = `user`.`id` and `id` = ?))) or ((`imageable_type` = ? and exists (select * from `book` where `images`.`imageable_id` = `book`.`id` and `id` = ?)))', ['user', 1, 'book', 1]],
            ['select * from `user` where `user`.`id` = ? limit 1', [1]],
            ['select * from `book` where `book`.`id` = ? limit 1', [1]],
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame([$event->sql, $event->bindings], array_shift($sqls));
            }
        }
    }

    public function testWhereDoesntHaveMorph()
    {
        $this->getContainer();
        $images = Image::query()
            ->whereDoesntHaveMorph(
                'imageable',
                [
                    User::class,
                    Book::class,
                ],
                function (Builder $query, $type) {
                    if ($type === User::class) {
                        $query->where('id', '<>', 1);
                    }
                    if ($type === Book::class) {
                        $query->where('id', '<>', 1);
                    }
                }
            )
            ->get();
        $res = $images->every(function ($item, $key) {
            return $item->imageable->id == 1;
        });
        $this->assertSame(true, $res);
        $sqls = [
            ['select * from `images` where ((`imageable_type` = ? and not exists (select * from `user` where `images`.`imageable_id` = `user`.`id` and `id` <> ?)) or (`imageable_type` = ? and not exists (select * from `book` where `images`.`imageable_id` = `book`.`id` and `id` <> ?)))', ['user', 1, 'book', 1]],
            ['select * from `user` where `user`.`id` = ? limit 1', [1]],
            ['select * from `book` where `book`.`id` = ? limit 1', [1]],
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame([$event->sql, $event->bindings], array_shift($sqls));
            }
        }
    }

    public function testOrWhereDoesntHaveMorph()
    {
        $this->getContainer();
        $images = Image::query()
            ->whereDoesntHaveMorph(
                'imageable',
                [
                    User::class,
                ],
                function (Builder $query) {
                    $query->where('id', '<>', 1);
                }
            )
            ->orWhereDoesntHaveMorph(
                'imageable',
                [
                    Book::class,
                ],
                function (Builder $query) {
                    $query->where('id', '<>', 1);
                }
            )
            ->get();
        $res = $images->every(function ($item, $key) {
            return $item->imageable->id == 1;
        });
        $this->assertSame(true, $res);
        $sqls = [
            ['select * from `images` where ((`imageable_type` = ? and not exists (select * from `user` where `images`.`imageable_id` = `user`.`id` and `id` <> ?))) or ((`imageable_type` = ? and not exists (select * from `book` where `images`.`imageable_id` = `book`.`id` and `id` <> ?)))', ['user', 1, 'book', 1]],
            ['select * from `user` where `user`.`id` = ? limit 1', [1]],
            ['select * from `book` where `book`.`id` = ? limit 1', [1]],
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame([$event->sql, $event->bindings], array_shift($sqls));
            }
        }
    }

    protected function getContainer()
    {
        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')->with(Mockery::any())->andReturnUsing(function ($event) {
            $this->channel->push($event);
        });
        $container = ContainerStub::getContainer(function ($conn) use ($dispatcher) {
            $conn->setEventDispatcher($dispatcher);
        });
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($dispatcher);

        return $container;
    }
}
