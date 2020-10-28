<?php


namespace Hyperf\Lock;


use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;

class SnowflakeIdGenerate implements IdGenerateInterface
{

    /**
     * @var SnowflakeIdGenerator
     */
    protected $snowflakeIdGenerator;

    public function __construct(SnowflakeIdGenerator $snowflakeIdGenerator)
    {
        $this->snowflakeIdGenerator = $snowflakeIdGenerator;
    }

    public function generate()
    {
        return $this->snowflakeIdGenerator->generate();
    }
}
