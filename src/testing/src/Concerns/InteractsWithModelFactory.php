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
namespace Hyperf\Testing\Concerns;

use Faker\Factory as FakerFactory;
use Hyperf\Testing\ModelFactory;

trait InteractsWithModelFactory
{
    protected ?ModelFactory $modelFactory = null;

    protected function setUpInteractsWithModelFactory()
    {
        if (! class_exists(FakerFactory::class)) {
            return;
        }

        $this->modelFactory = ModelFactory::create(
            FakerFactory::create('en_US')
        );

        if (is_dir($path = BASE_PATH . '/database/factories')) {
            $this->modelFactory->load($path);
        }

        $this->modelFactory->load($path);
    }
}
