<?php
declare(strict_types=1);

namespace Hyperf\Database\Factory;

use Faker\Factory as FakerFactory;
use Hyperf\Database\Model\Factory;

class FactoryResolver
{
    public function __invoke()
    {
        return Factory::construct(FakerFactory::create());
    }
}
