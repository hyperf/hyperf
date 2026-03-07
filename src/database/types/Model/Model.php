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

namespace Hyperf\Types\Model;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use User;

use function PHPStan\Testing\assertType;

function test(User $user, Post $post, Comment $comment, Article $article): void
{
    assertType('Hyperf\Database\Model\Builder<User>', User::query());
    assertType('Hyperf\Database\Model\Builder<User>', $user->newQuery());
    assertType('Hyperf\Database\Model\Builder<User>', $user->withTrashed());
    assertType('Hyperf\Database\Model\Builder<User>', $user->onlyTrashed());
    assertType('Hyperf\Database\Model\Builder<User>', $user->withoutTrashed());

    assertType('Hyperf\Database\Model\Collection<(int|string), User>', $user->newCollection([new User()]));
    assertType('Hyperf\Types\Model\Comments', $comment->newCollection([new Comment()]));
    assertType('Hyperf\Database\Model\Collection<(int|string), Hyperf\Types\Model\Post>', $post->newCollection(['foo' => new Post()]));
    assertType('Hyperf\Database\Model\Collection<(int|string), Hyperf\Types\Model\Article>', $article->newCollection([new Article()]));
    assertType('Hyperf\Types\Model\Comments', $comment->newCollection([new Comment()]));

    assertType('bool|null', $user->restore());
}

class Post extends Model
{
    protected static string $collectionClass = Posts::class;
}

/**
 * @template TKey of array-key
 * @template TModel of Post
 *
 * @extends Collection<TKey, TModel> */
class Posts extends Collection
{
}

final class Comment extends Model
{
    /** @param  array<array-key, Comment>  $models */
    public function newCollection(array $models = []): Comments
    {
        return new Comments($models);
    }
}

/** @extends Collection<array-key, Comment> */
final class Comments extends Collection
{
}

class Article extends Model
{
}

/**
 * @template TKey of array-key
 * @template TModel of Article
 *
 * @extends Collection<TKey, TModel> */
class Articles extends Collection
{
}
