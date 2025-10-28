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

namespace Hyperf\Types\Relations;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasManyThrough;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\Relations\HasOneThrough;
use Hyperf\Database\Model\Relations\MorphMany;
use Hyperf\Database\Model\Relations\MorphOne;
use Hyperf\Database\Model\Relations\MorphTo;
use Hyperf\Database\Model\Relations\MorphToMany;
use Hyperf\Database\Model\Relations\Relation;

use function PHPStan\Testing\assertType;

function test(User $user, Post $post, Comment $comment, ChildUser $child): void
{
    assertType('Hyperf\Database\Model\Relations\HasOne<Hyperf\Types\Relations\Address, Hyperf\Types\Relations\User>', $user->address());
    assertType('Hyperf\Types\Relations\Address|null', $user->address()->getResults());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Address>', $user->address()->get());
    assertType('Hyperf\Types\Relations\Address', $user->address()->make());
    assertType('Hyperf\Types\Relations\Address', $user->address()->create());
    assertType('Hyperf\Database\Model\Relations\HasOne<Hyperf\Types\Relations\Address, Hyperf\Types\Relations\ChildUser>', $child->address());
    assertType('Hyperf\Types\Relations\Address', $child->address()->make());
    assertType('Hyperf\Types\Relations\Address', $child->address()->create([]));
    assertType('Hyperf\Types\Relations\Address', $child->address()->getRelated());
    assertType('Hyperf\Types\Relations\ChildUser', $child->address()->getParent());

    assertType('Hyperf\Database\Model\Relations\HasMany<Hyperf\Types\Relations\Post, Hyperf\Types\Relations\User>', $user->posts());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Post>', $user->posts()->getResults());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Post>', $user->posts()->createMany([]));
    assertType('Hyperf\Types\Relations\Post', $user->posts()->make());
    assertType('Hyperf\Types\Relations\Post', $user->posts()->create());
    assertType('Hyperf\Types\Relations\Post|false', $user->posts()->save(new Post()));

    assertType("Hyperf\\Database\\Model\\Relations\\BelongsToMany<Hyperf\\Types\\Relations\\Role, Hyperf\\Types\\Relations\\User, Hyperf\\Database\\Model\\Relations\\Pivot, 'pivot'>", $user->roles());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}>', $user->roles()->getResults());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}>', $user->roles()->find([1]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}>', $user->roles()->findMany([1, 2, 3]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}>', $user->roles()->findOrNew([1]));
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}>', $user->roles()->findOrFail([1]));
    assertType('Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}', $user->roles()->findOrNew(1));
    assertType('Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}', $user->roles()->findOrFail(1));
    assertType('(Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot})|null', $user->roles()->find(1));
    assertType('(Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot})|null', $user->roles()->first());
    assertType('Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}', $user->roles()->firstOrNew([]));
    assertType('Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}', $user->roles()->firstOrFail());
    assertType('Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}', $user->roles()->firstOrCreate([]));
    assertType('Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}', $user->roles()->create());
    assertType('Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}', $user->roles()->updateOrCreate([]));
    assertType('Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}', $user->roles()->save(new Role()));
    $roles = $user->roles()->getResults();
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}>', $user->roles()->saveMany($roles));
    assertType('array<int, Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}>', $user->roles()->saveMany($roles->all()));
    assertType('array<int, Hyperf\Types\Relations\Role&object{pivot: Hyperf\Database\Model\Relations\Pivot}>', $user->roles()->createMany($roles->all()));
    assertType('array{attached: array<int|string>, detached: array<int|string>, updated: array<int|string>}', $user->roles()->sync($roles));
    assertType('array{attached: array<int|string>, detached: array<int|string>, updated: array<int|string>}', $user->roles()->syncWithoutDetaching($roles));

    assertType('Hyperf\Database\Model\Relations\HasOneThrough<Hyperf\Types\Relations\Car, Hyperf\Types\Relations\Mechanic, Hyperf\Types\Relations\User>', $user->car());
    assertType('Hyperf\Types\Relations\Car|null', $user->car()->getResults());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Car>', $user->car()->find([1]));
    assertType('Hyperf\Types\Relations\Car|null', $user->car()->find(1));
    assertType('Hyperf\Types\Relations\Car|null', $user->car()->first());

    assertType('Hyperf\Database\Model\Relations\HasManyThrough<Hyperf\Types\Relations\Part, Hyperf\Types\Relations\Mechanic, Hyperf\Types\Relations\User>', $user->parts());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Part>', $user->parts()->getResults());

    assertType('Hyperf\Database\Model\Relations\BelongsTo<Hyperf\Types\Relations\User, Hyperf\Types\Relations\Post>', $post->user());
    assertType('Hyperf\Types\Relations\User|null', $post->user()->getResults());
    assertType('Hyperf\Types\Relations\User', $post->user()->make());
    assertType('Hyperf\Types\Relations\User', $post->user()->create());
    assertType('Hyperf\Types\Relations\Post', $post->user()->associate(new User()));
    assertType('Hyperf\Types\Relations\Post', $post->user()->dissociate());
    assertType('Hyperf\Types\Relations\Post', $post->user()->getChild());

    assertType('Hyperf\Database\Model\Relations\MorphOne<Hyperf\Types\Relations\Image, Hyperf\Types\Relations\Post>', $post->image());
    assertType('Hyperf\Types\Relations\Image|null', $post->image()->getResults());
    assertType('Hyperf\Types\Relations\Image', $post->image()->forceCreate([]));

    assertType('Hyperf\Database\Model\Relations\MorphMany<Hyperf\Types\Relations\Comment, Hyperf\Types\Relations\Post>', $post->comments());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Comment>', $post->comments()->getResults());

    assertType('Hyperf\Database\Model\Relations\MorphTo<Hyperf\Database\Model\Model, Hyperf\Types\Relations\Comment>', $comment->commentable());
    assertType('Hyperf\Database\Model\Model|null', $comment->commentable()->getResults());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Comment>', $comment->commentable()->getEager());
    assertType('Hyperf\Database\Model\Model', $comment->commentable()->createModelByType('foo'));
    assertType('Hyperf\Types\Relations\Comment', $comment->commentable()->associate(new Post()));
    assertType('Hyperf\Types\Relations\Comment', $comment->commentable()->dissociate());

    assertType("Hyperf\\Database\\Model\\Relations\\MorphToMany<Hyperf\\Types\\Relations\\Tag, Hyperf\\Types\\Relations\\Post, Hyperf\\Database\\Model\\Relations\\MorphPivot, 'pivot'>", $post->tags());
    assertType('Hyperf\Database\Model\Collection<int, Hyperf\Types\Relations\Tag&object{pivot: Hyperf\Database\Model\Relations\MorphPivot}>', $post->tags()->getResults());

    assertType('int', Relation::noConstraints(fn () => 42));
}

class User extends Model
{
    /** @return HasOne<Address, $this> */
    public function address(): HasOne
    {
        $hasOne = $this->hasOne(Address::class);
        assertType('Hyperf\Database\Model\Relations\HasOne<Hyperf\Types\Relations\Address, $this(Hyperf\Types\Relations\User)>', $hasOne);

        return $hasOne;
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        $hasMany = $this->hasMany(Post::class);
        assertType('Hyperf\Database\Model\Relations\HasMany<Hyperf\Types\Relations\Post, $this(Hyperf\Types\Relations\User)>', $hasMany);

        return $hasMany;
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        $belongsToMany = $this->belongsToMany(Role::class);
        assertType('Hyperf\Database\Model\Relations\BelongsToMany<Hyperf\Types\Relations\Role, $this(Hyperf\Types\Relations\User), Hyperf\Database\Model\Relations\Pivot, \'pivot\'>', $belongsToMany);

        return $belongsToMany;
    }

    /** @return HasOne<Mechanic, $this> */
    public function mechanic(): HasOne
    {
        return $this->hasOne(Mechanic::class);
    }

    /** @return HasMany<Mechanic, $this> */
    public function mechanics(): HasMany
    {
        return $this->hasMany(Mechanic::class);
    }

    /** @return HasOneThrough<Car, Mechanic, $this> */
    public function car(): HasOneThrough
    {
        $hasOneThrough = $this->hasOneThrough(Car::class, Mechanic::class);
        assertType('Hyperf\Database\Model\Relations\HasOneThrough<Hyperf\Types\Relations\Car, Hyperf\Types\Relations\Mechanic, $this(Hyperf\Types\Relations\User)>', $hasOneThrough);

        return $hasOneThrough;
    }

    /** @return HasManyThrough<Part, Mechanic, $this> */
    public function parts(): HasManyThrough
    {
        $hasManyThrough = $this->hasManyThrough(Part::class, Mechanic::class);
        assertType('Hyperf\Database\Model\Relations\HasManyThrough<Hyperf\Types\Relations\Part, Hyperf\Types\Relations\Mechanic, $this(Hyperf\Types\Relations\User)>', $hasManyThrough);

        return $hasManyThrough;
    }
}

class Post extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class);
        assertType('Hyperf\Database\Model\Relations\BelongsTo<Hyperf\Types\Relations\User, $this(Hyperf\Types\Relations\Post)>', $belongsTo);

        return $belongsTo;
    }

    /** @return MorphOne<Image, $this> */
    public function image(): MorphOne
    {
        $morphOne = $this->morphOne(Image::class, 'imageable');
        assertType('Hyperf\Database\Model\Relations\MorphOne<Hyperf\Types\Relations\Image, $this(Hyperf\Types\Relations\Post)>', $morphOne);

        return $morphOne;
    }

    /** @return MorphMany<Comment, $this> */
    public function comments(): MorphMany
    {
        $morphMany = $this->morphMany(Comment::class, 'commentable');
        assertType('Hyperf\Database\Model\Relations\MorphMany<Hyperf\Types\Relations\Comment, $this(Hyperf\Types\Relations\Post)>', $morphMany);

        return $morphMany;
    }

    /** @return MorphToMany<Tag, $this> */
    public function tags(): MorphToMany
    {
        $morphToMany = $this->morphedByMany(Tag::class, 'taggable');
        assertType('Hyperf\Database\Model\Relations\MorphToMany<Hyperf\Types\Relations\Tag, $this(Hyperf\Types\Relations\Post), Hyperf\Database\Model\Relations\MorphPivot, \'pivot\'>', $morphToMany);

        return $morphToMany;
    }
}

class Comment extends Model
{
    /** @return MorphTo<Model, $this> */
    public function commentable(): MorphTo
    {
        $morphTo = $this->morphTo();
        assertType('Hyperf\Database\Model\Relations\MorphTo<Hyperf\Database\Model\Model, $this(Hyperf\Types\Relations\Comment)>', $morphTo);

        return $morphTo;
    }
}

class Tag extends Model
{
    /** @return MorphToMany<Post, $this> */
    public function posts(): MorphToMany
    {
        $morphToMany = $this->morphToMany(Post::class, 'taggable');
        assertType('Hyperf\Database\Model\Relations\MorphToMany<Hyperf\Types\Relations\Post, $this(Hyperf\Types\Relations\Tag), Hyperf\Database\Model\Relations\MorphPivot, \'pivot\'>', $morphToMany);

        return $morphToMany;
    }
}

class Mechanic extends Model
{
    /** @return HasOne<Car, $this> */
    public function car(): HasOne
    {
        return $this->hasOne(Car::class);
    }

    /** @return HasMany<Part, $this> */
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}

class ChildUser extends User
{
}
class Address extends Model
{
}
class Role extends Model
{
}
class Car extends Model
{
}
class Part extends Model
{
}
class Image extends Model
{
}
