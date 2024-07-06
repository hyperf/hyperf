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

use function Hyperf\Support\optional;

class AuthorResourceWithOptionalRelationship extends PostResource
{
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'posts_count' => $this->whenLoaded('posts', function () {
                return $this->posts->count() . ' posts';
            }, function () {
                return 'not loaded';
            }),
            'latest_post_title' => $this->whenLoaded('posts', function () {
                return optional($this->posts->first())->title ?: 'no posts yet';
            }, 'not loaded'),
        ];
    }
}
