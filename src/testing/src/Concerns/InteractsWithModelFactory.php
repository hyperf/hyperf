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
use Hyperf\Database\Model\Factory;
use Hyperf\Testing\ModelFactory;

trait InteractsWithModelFactory
{
    protected ?ModelFactory $modelFactory = null;

    /**
     * @var string|string[]
     */
    protected $factoryPath = BASE_PATH . '/database/factories';

    protected function setUpInteractsWithModelFactory()
    {
        if (! class_exists(Factory::class) || ! class_exists(FakerFactory::class)) {
            return;
        }

        $this->modelFactory = ModelFactory::create(
            FakerFactory::create('en_US')
        );

        foreach ((array) $this->factoryPath as $path) {
            if (is_dir($path)) {
                $this->modelFactory->load($path);
            }
        }
    }

    protected function tearDownInteractsWithModelFactory()
    {
        $this->modelFactory = null;
    }
}
