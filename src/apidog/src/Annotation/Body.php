<?php
declare(strict_types = 1);
namespace Hyperf\Apidog\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Utils\Arr;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Body extends Param
{
    public $in = 'body';
    public $name = 'body';
    public $rules;
    public $description = 'body';

    public function __construct($value = null)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $val;
                }
            }
        }
        $this->setRquire()->setType()->rules2schema();
    }

    public function setRquire()
    {

        $this->required = strpos(json_encode($this->rules), 'required') !== false;
        return $this;
    }

    public function setType()
    {
        $this->type = '';

        return $this;
    }

    public function rules2schema()
    {
        $schema = [
            'type' => 'object',
            'required' => [],
            'properties' => [],
        ];
        foreach ($this->rules as $field => $rule) {
            $property = [];
            $field_name_label = explode('|', $field);
            $field_name = $field_name_label[0];
            if (!is_array($rule)) {
                $type = $this->getTypeByRule($rule);
            } else {
                //TODO 结构体多层
                $type = 'string';
            }
            $property['type'] = $type;
            $property['description'] = $field_name_label[1] ?? '';
            $schema['properties'][$field_name] = $property;
        }

        $this->schema = $schema;

        return $this;
    }

    public function getTypeByRule($rule)
    {
        $default = explode('|', preg_replace('/\[.*\]/', '', $rule));
        if (array_intersect($default, ['int', 'lt', 'gt', 'ge'])) {
            return 'integer';
        }
        if (array_intersect($default, ['array'])) {
            return 'array';
        }
        return 'string';
    }
}
