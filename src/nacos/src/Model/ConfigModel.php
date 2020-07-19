<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Nacos\Model;

use Hyperf\Utils\Codec\Xml;

class ConfigModel extends AbstractModel
{
    public $tenant;

    public $dataId;

    public $group;

    public $content;

    public $type = 'json';

    public $required_field = [
        'dataId',
    ];

    public function parser($config_origin)
    {
        switch ($this->type) {
            case 'json':
                return is_array($config_origin) ? $config_origin : json_decode($config_origin, true);
            case 'yml':
                return is_array($config_origin) ? $config_origin : yaml_parse($config_origin);
            case 'xml':
                return Xml::toArray($config_origin);
            default:
                return $config_origin;
        }
    }
}
