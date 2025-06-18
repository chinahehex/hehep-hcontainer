<?php
namespace hehe\core\hcontainer\ann\base;

use Doctrine\Common\Annotations\AnnotationReader;
use hehe\core\hcontainer\ann\ScanManager;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;


/**
 * Doctrine注解解析类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class DoctrineAnnotationParser
{
    /**
     * @var AnnotationReader
     */
    protected $annotationReader;


    public function __construct()
    {
        if (class_exists(AnnotationReader::class)) {
            $this->annotationReader = new AnnotationReader();
        }
    }

    public function getAnnotationMeta($myAnnotation):?Annotation
    {
        $reflectionClass = new ReflectionClass(get_class($myAnnotation));
        return $this->annotationReader->getClassAnnotation($reflectionClass,Annotation::class);
    }

    public function getClassAnnotations(ReflectionClass $reflectionClass):array
    {
        if (is_null($this->annotationReader)) {
            return [];
        }

        return $this->annotationReader->getClassAnnotations($reflectionClass);
    }

    public function findClassAnnotations(string $class, string $targetAnnotation):array
    {
        $reflectionClass = new ReflectionClass($class);
        $annotations = $this->getClassAnnotations($reflectionClass);
        $targetAnnotations = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $targetAnnotation) {
                $targetAnnotations[] = $annotation;
            }
        }

        return $targetAnnotations;
    }

    public function getMethodAnnotations(ReflectionMethod $reflectionMethod):array
    {
        if (is_null($this->annotationReader)) {
            return [];
        }

        return $this->annotationReader->getMethodAnnotations($reflectionMethod);
    }

    public function findMethodAnnotations(string $class, string $method, string $targetAnnotation):array
    {
        $reflectionMethod = new ReflectionMethod($class, $method);
        $annotations = $this->getMethodAnnotations($reflectionMethod);
        $targetAnnotations = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $targetAnnotation) {
                $targetAnnotations[] = $annotation;
            }
        }

        return $targetAnnotations;
    }

    public function getPropertyAnnotations(ReflectionProperty $propertie):array
    {
        if (is_null($this->annotationReader)) {
            return [];
        }

        return $this->annotationReader->getPropertyAnnotations($propertie);
    }

    public function findPropertyAnnotations(string $class, string $property, string $targetAnnotation):array
    {
        $reflectionProperty = new ReflectionProperty($class, $property);
        $annotations = $this->getPropertyAnnotations($reflectionProperty);
        $targetAnnotations = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $targetAnnotation) {
                $targetAnnotations[] = $annotation;
            }
        }

        return $targetAnnotations;
    }

}
