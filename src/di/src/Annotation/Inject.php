<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Annotation;

use Hyperf\Di\ReflectionManager;
use PhpDocReader\AnnotationException;
use PhpDocReader\PhpDocReader;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Inject extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var boolean
     */
    public $require;
    /**
     * @var PhpDocReader
     */
    private $docReader;

    public function __construct($value = null)
    {

        if(!isset($value['require'])){
            $value['require'] = false;
        }
        parent::__construct($value);
        $this->docReader = make(PhpDocReader::class);
    }

    public function collectProperty(string $className, ?string $target): void
    {
        if($this->require){
            $this->value = $this->docReader->getPropertyClass(ReflectionManager::reflectClass($className)->getProperty($target));
            AnnotationCollector::collectProperty($className, $target, static::class, $this);
        }else{
            try{
                $this->value = $this->docReader->getPropertyClass(ReflectionManager::reflectClass($className)->getProperty($target));
                AnnotationCollector::collectProperty($className, $target, static::class, $this);
            }catch (AnnotationException $e){
                $this->value = '';
            }
        }

    }
}
