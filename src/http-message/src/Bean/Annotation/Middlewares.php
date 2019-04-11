<?php

namespace Hyperf\Http\Message\Bean\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Middlewares annotation
 *
 * @Annotation
 * @Target({"ALL"})
 */
class Middlewares
{

    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * @var string
     */
    private $group = '';

    /**
     * Middlewares constructor.
     *
     * @param array $values
     */
    public function __construct($values)
    {
        if (isset($values['value'])) {
            $this->middlewares = $values['value'];
        }
        if (isset($values['middlewares'])) {
            $this->middlewares = $values['value'];
        }
        if (isset($values['group'])) {
            $this->group = $values['value'];
        }
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param array $middlewares
     * @return Middlewares
     */
    public function setMiddlewares($middlewares)
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }
}
