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

namespace HyperfTest\Resource\Stubs\Resources;

use Hyperf\Resource\Json\ResourceCollection;
use Hyperf\Resource\Response\PaginationInformationInterface;

class PaginationCollection extends ResourceCollection implements PaginationInformationInterface
{
    public function paginationInformation(array $paginated, array $default): array
    {
        return [];
    }
}
