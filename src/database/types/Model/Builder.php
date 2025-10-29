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

namespace Hyperf\Types\Builder;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\MorphTo;

use function PHPStan\Testing\assertType;

/** @param Builder<User> $query */
function test(
    Builder $query,
    User $user,
    Post $post,
    ChildPost $childPost,
    Comment $comment
): void {
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->where('id', 1));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orWhere('name', 'John'));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->with('relation'));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->with(['relation' => ['foo' => fn ($q) => $q]]));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->with(['relation' => function ($query) {
        // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
    }]));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->without('relation'));
    assertType('array<int, Hyperf\Types\Builder\User>', $query->getModels());
    assertType('array<int, Hyperf\Types\Builder\User>', $query->eagerLoadRelations([]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $query->get());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $query->hydrate([]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $query->fromQuery('foo', []));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $query->findMany([1, 2, 3]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $query->findOrFail([1]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $query->findOrNew([1]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $query->find([1]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $query->findOr([1], callback: fn () => 42));
    assertType('Hyperf\Types\Builder\User', $query->findOrFail(1));
    assertType('Hyperf\Types\Builder\User|null', $query->find(1));
    assertType('Hyperf\Types\Builder\User|int', $query->findOr(1, fn () => 42));
    assertType('Hyperf\Types\Builder\User|int', $query->findOr(1, callback: fn () => 42));
    assertType('Hyperf\Types\Builder\User|null', $query->first());
    assertType('Hyperf\Types\Builder\User|int', $query->firstOr(fn () => 42));
    assertType('Hyperf\Types\Builder\User|int', $query->firstOr(callback: fn () => 42));
    assertType('Hyperf\Types\Builder\User', $query->firstOrNew(['id' => 1]));
    assertType('Hyperf\Types\Builder\User', $query->findOrNew(1));
    assertType('Hyperf\Types\Builder\User', $query->firstOrCreate(['id' => 1]));
    assertType('Hyperf\Types\Builder\User', $query->createOrFirst(['id' => 1]));
    assertType('Hyperf\Types\Builder\User', $query->create(['name' => 'John']));
    assertType('Hyperf\Types\Builder\User', $query->forceCreate(['name' => 'John']));
    assertType('Hyperf\Types\Builder\User', $query->getModel());
    assertType('Hyperf\Types\Builder\User', $query->make(['name' => 'John']));
    assertType('Hyperf\Types\Builder\User', $query->forceCreate(['name' => 'John']));
    assertType('Hyperf\Types\Builder\User', $query->updateOrCreate(['id' => 1], ['name' => 'John']));
    assertType('Hyperf\Types\Builder\User', $query->firstOrFail());
    assertType('Hyperf\Types\Builder\User', $query->sole());
    assertType('Generator<int, Hyperf\Types\Builder\User, Hyperf\Types\Builder\User, void>', $query->cursor());
    assertType('Hyperf\Collection\LazyCollection<int, Hyperf\Types\Builder\User>', $query->lazy());
    assertType('Hyperf\Collection\LazyCollection<int, Hyperf\Types\Builder\User>', $query->lazyById());
    assertType('Hyperf\Collection\LazyCollection<int, Hyperf\Types\Builder\User>', $query->lazyByIdDesc());
    assertType('Hyperf\Collection\Collection<(int|string), mixed>', $query->pluck('foo'));
    assertType('Hyperf\Database\Model\Relations\Relation<Hyperf\Database\Model\Model, Hyperf\Types\Builder\User, *>', $query->getRelation('foo'));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $query->setModel(new Post()));

    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->has('foo', callback: function ($query) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Database\Model\Model>', $query);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->has($user->posts(), callback: function ($query) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $query);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orHas($user->posts()));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->doesntHave($user->posts(), callback: function ($query) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $query);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orDoesntHave($user->posts()));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->whereHas($user->posts(), function ($query) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $query);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->withWhereHas('posts', function ($query) {
        assertType('Hyperf\Database\Model\Builder<*>|Hyperf\Database\Model\Relations\Relation<*, *, *>', $query);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orWhereHas($user->posts(), function ($query) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $query);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->whereDoesntHave($user->posts(), function ($query) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $query);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orWhereDoesntHave($user->posts(), function ($query) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $query);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->hasMorph($post->taggable(), 'taggable', callback: function ($query, $type) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Database\Model\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orHasMorph($post->taggable(), 'taggable'));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->doesntHaveMorph($post->taggable(), 'taggable', callback: function ($query, $type) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Database\Model\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orDoesntHaveMorph($post->taggable(), 'taggable'));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->whereHasMorph($post->taggable(), 'taggable', function ($query, $type) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Database\Model\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orWhereHasMorph($post->taggable(), 'taggable', function ($query, $type) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Database\Model\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->whereDoesntHaveMorph($post->taggable(), 'taggable', function ($query, $type) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Database\Model\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orWhereDoesntHaveMorph($post->taggable(), 'taggable', function ($query, $type) {
        assertType('Hyperf\Database\Model\Builder<Hyperf\Database\Model\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->whereRelation($user->posts(), 'id', 1));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orWhereRelation($user->posts(), 'id', 1));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->whereMorphRelation($post->taggable(), 'taggable', 'id', 1));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\User>', $query->orWhereMorphRelation($post->taggable(), 'taggable', 'id', 1));

    $query->chunk(1, function ($users, $page) {
        assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $users);
        assertType('int', $page);
    });
    $query->chunkById(1, function ($users, $page) {
        assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $users);
        assertType('int', $page);
    });
    $query->chunkMap(function ($users) {
        assertType('Hyperf\Types\Builder\User', $users);
    });
    $query->chunkByIdDesc(1, function ($users, $page) {
        assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Builder\User>', $users);
        assertType('int', $page);
    });
    $query->each(function ($users, $page) {
        assertType('Hyperf\Types\Builder\User', $users);
        assertType('int', $page);
    });
    $query->eachById(function ($users, $page) {
        assertType('Hyperf\Types\Builder\User', $users);
        assertType('int', $page);
    });

    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', Post::query());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', Post::on());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', Post::onWriteConnection());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', Post::with([]));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $post->newQuery());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $post->newModelQuery());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $post->newQueryWithoutRelationships());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $post->newQueryWithoutScopes());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $post->newQueryWithoutScope('foo'));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $post->newQueryForRestoration(1));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Post>', $post->newQuery()->where('foo', 'bar'));
    assertType('Hyperf\Types\Builder\Post', $post->newQuery()->create(['name' => 'John']));

    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', ChildPost::query());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', ChildPost::on());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', ChildPost::onWriteConnection());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', ChildPost::with([]));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', $childPost->newQuery());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', $childPost->newModelQuery());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', $childPost->newQueryWithoutRelationships());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', $childPost->newQueryWithoutScopes());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', $childPost->newQueryWithoutScope('foo'));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', $childPost->newQueryForRestoration(1));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\ChildPost>', $childPost->newQuery()->where('foo', 'bar'));
    assertType('Hyperf\Types\Builder\ChildPost', $childPost->newQuery()->create(['name' => 'John']));

    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', Comment::query());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', Comment::on());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', Comment::onWriteConnection());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', Comment::with([]));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', $comment->newQuery());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', $comment->newModelQuery());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', $comment->newQueryWithoutRelationships());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', $comment->newQueryWithoutScopes());
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', $comment->newQueryWithoutScope('foo'));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', $comment->newQueryForRestoration(1));
    assertType('Hyperf\Database\Model\Builder<Hyperf\Types\Builder\Comment>', $comment->newQuery()->where('foo', 'bar'));
    assertType('Hyperf\Types\Builder\Comment', $comment->newQuery()->create(['name' => 'John']));
}

class User extends Model
{
    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}

class ChildPost extends Post
{
}

class Comment extends Model
{
}

/**
 * @template TModel of \Hyperf\Database\Model\Model
 *
 * @extends \Hyperf\Database\Model\Builder<TModel>
 */
class CommonBuilder extends Builder
{
    public function foo(): static
    {
        return $this->where('foo', 'bar');
    }
}

/** @extends CommonBuilder<Comment> */
class CommentBuilder extends CommonBuilder
{
}
