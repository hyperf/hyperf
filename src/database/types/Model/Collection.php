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
use function PHPStan\Testing\assertType;

$collection = User::all();
assertType('Hyperf\Database\Model\Collection<int, User>', $collection);

assertType('User|null', $collection->find(1));
assertType('\'string\'|User', $collection->find(1, 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->find([1]));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->load('string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->load(['string']));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->load(['string' => ['foo' => fn ($q) => $q]]));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->load(['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}]));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAggregate('string', 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAggregate(['string'], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAggregate(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAggregate(['string'], 'string', 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAggregate(['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}], 'string', 'string'));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadCount('string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadCount(['string']));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadCount(['string' => ['foo' => fn ($q) => $q]]));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadCount(['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}]));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMax('string', 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMax(['string'], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMax(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMax(['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}], 'string'));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMin('string', 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMin(['string'], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMin(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMin(['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}], 'string'));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadSum('string', 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadSum(['string'], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadSum(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadSum(['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}], 'string'));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAvg('string', 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAvg(['string'], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAvg(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadAvg(['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}], 'string'));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMissing('string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMissing(['string']));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMissing(['string' => ['foo' => fn ($q) => $q]]));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMissing(['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}]));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMorph('string', ['string']));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMorph('string', ['string' => ['foo' => fn ($q) => $q]]));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMorph('string', ['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}]));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMorphCount('string', ['string']));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMorphCount('string', ['string' => ['foo' => fn ($q) => $q]]));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->loadMorphCount('string', ['string' => function ($query) {
    // assertType('Hyperf\Database\Model\Relations\Relation<*,*,*>', $query);
}]));

assertType('bool', $collection->contains(function ($user) {
    assertType('User', $user);

    return true;
}));
assertType('bool', $collection->contains('string', '=', 'string'));

asserttype('array<int, (int|string)>', $collection->modelKeys());

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->merge($collection));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->merge([new User()]));

assertType(
    'Hyperf\Database\Model\Collection<int, User>',
    $collection->map(function ($user, $int) {
        assertType('User', $user);
        assertType('int', $int);

        return new User();
    })
);
assertType(
    'Hyperf\Collection\Collection<int, string>',
    $collection->map(function ($user, $int) {
        assertType('User', $user);
        assertType('int', $int);

        return 'string';
    })
);

assertType(
    'Hyperf\Database\Model\Collection<int, User>',
    $collection->fresh()
);
assertType(
    'Hyperf\Database\Model\Collection<int, User>',
    $collection->fresh('string')
);
assertType(
    'Hyperf\Database\Model\Collection<int, User>',
    $collection->fresh(['string'])
);

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->diff($collection));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->diff([new User()]));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->intersect($collection));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->intersect([new User()]));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->unique());
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->unique(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return $user->getTable();
}));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->unique('string'));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->only(null));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->only(['string']));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->except(null));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->except(['string']));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->makeHidden('string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->makeHidden(['string']));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->makeVisible('string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->makeVisible(['string']));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->append('string'));
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->append(['string']));

assertType('Hyperf\Database\Model\Collection<int, User>', $collection->unique());
assertType('Hyperf\Database\Model\Collection<int, User>', $collection->uniqueStrict());

assertType('array<User>', $collection->getDictionary());
assertType('array<User>', $collection->getDictionary($collection));
assertType('array<User>', $collection->getDictionary([new User()]));

assertType('Hyperf\Collection\Collection<(int|string), mixed>', $collection->pluck('string'));
assertType('Hyperf\Collection\Collection<(int|string), mixed>', $collection->pluck(['string']));

assertType('Hyperf\Collection\Collection<int, int>', $collection->keys());

assertType('Hyperf\Collection\Collection<int, Hyperf\Collection\Collection<int, int|User>>', $collection->zip([1]));
assertType('Hyperf\Collection\Collection<int, Hyperf\Collection\Collection<int, string|User>>', $collection->zip(['string']));

assertType('Hyperf\Collection\Collection<int, mixed>', $collection->collapse());

assertType('Hyperf\Collection\Collection<int, mixed>', $collection->flatten());
assertType('Hyperf\Collection\Collection<int, mixed>', $collection->flatten(4));

assertType('Hyperf\Collection\Collection<User, int>', $collection->flip());

assertType('Hyperf\Collection\Collection<int, int|User>', $collection->pad(2, 0));
assertType('Hyperf\Collection\Collection<int, string|User>', $collection->pad(2, 'string'));
