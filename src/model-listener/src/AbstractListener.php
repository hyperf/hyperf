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

namespace Hyperf\ModelListener;

use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Database\Model\Events\ForceDeleted;
use Hyperf\Database\Model\Events\Restored;
use Hyperf\Database\Model\Events\Restoring;
use Hyperf\Database\Model\Events\Retrieved;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Events\Saving;
use Hyperf\Database\Model\Events\Updated;
use Hyperf\Database\Model\Events\Updating;

/**
 * @method retrieved(Retrieved $event)
 * @method creating(Creating $event)
 * @method created(Created $event)
 * @method updating(Updating $event)
 * @method updated(Updated $event)
 * @method saving(Saving $event)
 * @method saved(Saved $event)
 * @method restoring(Restoring $event)
 * @method restored(Restored $event)
 * @method deleting(Deleting $event)
 * @method deleted(Deleted $event)
 * @method forceDeleted(ForceDeleted $event)
 */
abstract class AbstractListener
{
}
