<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ConfigApollo;

interface ClientInterface
{
    /**
     * Pull the config values from configuration center, and then update the Config values.
     *
     * @param array $namespaces the namespaces of configs that you want to pull
     * @param array $callbacks the method level callbacks, will execute these callbacks after the config values pulled
     */
    public function pull(array $namespaces, array $callbacks = []): void;

    public function getOption(): Option;
}
