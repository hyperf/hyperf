<?php
namespace Hyperf\Apidog\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

abstract class Param extends AbstractAnnotation
{

    public $in;
    public $key;
    public $rule;
    public $default;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->setName()->setDescription()->setRquire()->setType();
    }

    public function setName()
    {
        $this->name = explode('|', $this->key)[0];

        return $this;
    }

    public function setDescription()
    {
        $this->description = explode('|', $this->key)[1] ?? '';

        return $this;
    }

    public function setRquire()
    {
        $this->required = strpos($this->rule, 'required') !== false;

        return $this;
    }

    public function setType()
    {
        $type = 'string';
        if (strpos($this->rule, 'int') !== false) {
            $type = 'integer';
        }
        $this->type = $type;

        return $this;
    }
}