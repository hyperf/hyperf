<?php

namespace Hyperf\Http\Message\Bean\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Helper\StringHelper;

/**
 * Middleware annotation
 *
 * @Annotation
 * @Target({"ALL"})
 */
class Middleware
{

    /**
     * @var string
     */
    private $class = '';

    /**
     * Middleware constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->class = $this->ltrimClass($values['value']);
        }
        if (isset($values['class'])) {
            $this->class = $this->ltrimClass($values['class']);
        }
    }

    /**
     * @param string $value
     * @return string
     */
    protected function ltrimClass(string $value)
    {
        return StringHelper::startsWith($value, '\\') ? substr($value, 1) : $value;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
}
