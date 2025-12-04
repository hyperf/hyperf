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
use Hyperf\Database\Model\ModelNotFoundException;

use function PHPStan\Testing\assertType;

/** @var ModelNotFoundException<User> $exception */
$exception = new ModelNotFoundException();

assertType('array<int, int|string>', $exception->getIds());
assertType('class-string<User>|null', $exception->getModel());

$exception->setModel(User::class, 1);
$exception->setModel(User::class, [1]);
$exception->setModel(User::class, '1');
$exception->setModel(User::class, ['1']);
