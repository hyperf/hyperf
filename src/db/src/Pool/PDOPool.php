<?php
declare(strict_types=1);

namespace Hyperf\DB\Pool;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\DB\Frequency;
use Hyperf\DB\PDOConnection;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

class PDOPool extends AbstractPool
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, string $name)
    {
        $this->name = $name;
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('database.%s', $this->name);
        if (! $config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);
        $options = Arr::get($this->config, 'pool', []);
        $this->frequency = make(Frequency::class);

        parent::__construct($container, $options);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    protected function createConnection(): ConnectionInterface
    {
        return new PDOConnection($this->container, $this, $this->config);
    }

}