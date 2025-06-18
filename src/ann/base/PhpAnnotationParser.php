<?php
namespace hehe\core\hcontainer\ann\base;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;


/**
 * PHP原生注解解析类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class PhpAnnotationParser
{

    public function getAnnotationMeta($myAnnotation)
    {
        $reflectionClass = new ReflectionClass(get_class($myAnnotation));

        if ($reflectionClass->getName() === Annotation::class) {
            return null;
        }

        if (method_exists($reflectionClass, 'getAttributes')) {
            $attributes = $reflectionClass->getAttributes();
            foreach ($attributes as $attribute) {
                $annotation = $attribute->newInstance();
                if ($annotation instanceof Annotation) {
                    return $annotation;
                }
            }
        }

        return null;
    }

    public function getClassAnnotations(ReflectionClass $reflectionClass):array
    {
        $annotations = [];
        if (method_exists($reflectionClass,'getAttributes')) {
            $attributes = $reflectionClass->getAttributes();
            if ($reflectionClass->getName() === Annotation::class || empty($attributes)) {
                return $annotations;
            }

            foreach ($attributes as $attribute) {
                $myAnnotation = $attribute->newInstance();
                if ($myAnnotation instanceof \Attribute || $myAnnotation instanceof Annotation) {
                    continue;
                }

                $annotations[] = $myAnnotation;
            }
        }

        return $annotations;
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
        $annotations = [];
        if (method_exists($reflectionMethod,'getAttributes')) {
            $attributes = $reflectionMethod->getAttributes();
            if ($reflectionMethod->getName() === Annotation::class || empty($attributes)) {
                return $annotations;
            }

            foreach ($attributes as $attribute) {
                $myAnnotation = $attribute->newInstance();
                if ($myAnnotation instanceof \Attribute || $myAnnotation instanceof Annotation) {
                    continue;
                }

                $annotations[] = $myAnnotation;
            }
        }

        return $annotations;
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
        $annotations = [];
        if (method_exists($propertie,'getAttributes')) {
            $attributes = $propertie->getAttributes();
            if ($propertie->getName() === Annotation::class || empty($attributes)) {
                return $annotations;
            }

            foreach ($attributes as $attribute) {
                $myAnnotation = $attribute->newInstance();
                if ($myAnnotation instanceof \Attribute || $myAnnotation instanceof Annotation) {
                    continue;
                }

                $annotations[] = $myAnnotation;
            }
        }

        return $annotations;
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
