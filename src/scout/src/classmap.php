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
if (class_exists('Elastic\Elasticsearch\Client') && ! class_exists($alias = 'Elasticsearch\Client')) {
    class_alias('Elastic\Elasticsearch\Client', $alias, true);
}

if (class_exists('Elasticsearch\Client') && ! class_exists($alias = 'Elastic\Elasticsearch\Client')) {
    class_alias('Elasticsearch\Client', $alias, true);
}
