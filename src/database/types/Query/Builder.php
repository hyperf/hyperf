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

namespace Hyperf\Types\Query\Builder;

use Hyperf\Database\Model\Builder as EloquentBuilder;
use Hyperf\Database\Query\Builder;
use User;

use function PHPStan\Testing\assertType;

/** @param EloquentBuilder<User> $userQuery */
function test(Builder $query, EloquentBuilder $userQuery): void
{
    assertType('object|null', $query->first());
    assertType('object|null', $query->find(1));
    assertType('Hyperf\Database\Query\Builder', $query->selectSub($userQuery, 'alias'));
    assertType('Hyperf\Database\Query\Builder', $query->fromSub($userQuery, 'alias'));
    assertType('Hyperf\Database\Query\Builder', $query->joinSub($userQuery, 'alias', 'foo'));
    assertType('Hyperf\Database\Query\Builder', $query->joinLateral($userQuery, 'alias'));
    assertType('Hyperf\Database\Query\Builder', $query->leftJoinLateral($userQuery, 'alias'));
    assertType('Hyperf\Database\Query\Builder', $query->leftJoinSub($userQuery, 'alias', 'foo'));
    assertType('Hyperf\Database\Query\Builder', $query->rightJoinSub($userQuery, 'alias', 'foo'));
    assertType('Hyperf\Database\Query\Builder', $query->orderBy($userQuery));
    assertType('Hyperf\Database\Query\Builder', $query->orderByDesc($userQuery));
    assertType('Hyperf\Database\Query\Builder', $query->union($userQuery));
    assertType('Hyperf\Database\Query\Builder', $query->unionAll($userQuery));
    assertType('bool', $query->insertUsing([], $userQuery));
    assertType('int', $query->insertOrIgnoreUsing([], $userQuery));
    assertType('Hyperf\Collection\LazyCollection<int, object>', $query->lazy());
    assertType('Hyperf\Collection\LazyCollection<int, object>', $query->lazyById());
    assertType('Hyperf\Collection\LazyCollection<int, object>', $query->lazyByIdDesc());

    $query->chunk(1, function ($users, $page) {
        assertType('Hyperf\Collection\Collection<int, object>', $users);
        assertType('int', $page);
    });
    $query->chunkById(1, function ($users, $page) {
        assertType('Hyperf\Collection\Collection<int, object>', $users);
        assertType('int', $page);
    });
    $query->chunkMap(function ($users) {
        assertType('object', $users);
    });
    $query->chunkByIdDesc(1, function ($users, $page) {
        assertType('Hyperf\Collection\Collection<int, object>', $users);
        assertType('int', $page);
    });
    $query->each(function ($users, $page) {
        assertType('object', $users);
        assertType('int', $page);
    });
    $query->eachById(function ($users, $page) {
        assertType('object', $users);
        assertType('int', $page);
    });
}
