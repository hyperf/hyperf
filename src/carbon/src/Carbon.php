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

namespace Hyperf\Carbon;

use DateTime;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;

class Carbon extends \Carbon\Carbon
{
    /**
     * Create a Carbon instance from a given ordered UUID or ULID.
     */
    public static function createFromId(string|Ulid|Uuid $id): static
    {
        if (is_string($id)) {
            $id = Ulid::isValid($id) ? Ulid::fromString($id) : Uuid::fromString($id);
        }

        return new static(DateTime::createFromInterface($id->getDateTime()));
    }
}
