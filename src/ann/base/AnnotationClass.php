<?php
namespace hehe\core\hcontainer\ann\base;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

/**
 * 注解解析处理
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class AnnotationClass
{
    /**
     * 目标类
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $clazz = '';

    /**
     * 目标类
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $reflectionClass = '';
    
    /**
     * 注解解析类
     * @var AnnotationParser
     */
    protected $annotationParser;

    public function __construct(string $clazz,annotationParser $annotationParser)
    {
        $this->clazz = $clazz;
        $this->annotationParser = $annotationParser;

        $this->reflectionClass = new ReflectionClass($this->clazz);
    }

    /**
     * 解析类所有注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function parse()
    {
        $this->parseClassAnnotation();
        $this->parseMethodAnnotation();
        $this->parseAttributeAnnotation();
    }

    /**
     * 解析类注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function parseClassAnnotation():void
    {

        $this->parseDoctrineClassAnnotation($this->reflectionClass);
        $this->parsePhpClassAnnotation($this->reflectionClass);
    }

    protected function parseDoctrineClassAnnotation(ReflectionClass $reflectionClass)
    {
        $classAnnotations  = $this->annotationParser->getClassAnnotations($reflectionClass);
        foreach ($classAnnotations as $myAnnotation) {
            $annotationMeta = $this->annotationParser->getAnnotationMeta($myAnnotation);
            if ($annotationMeta != null && $annotationMeta->effectiveTarget(Annotation::TARGET_CLASS)) {
                $annotationProcessor = $this->annotationParser->getAnnotationProcessor($annotationMeta);
                if ($annotationProcessor !== null) {
                    $annotationProcessor->handlerClazz($myAnnotation,$this->clazz);
                }
            }
        }
    }

    protected function parsePhpClassAnnotation(ReflectionClass $reflectionClass)
    {
        // php8 注解
        if (method_exists($reflectionClass,'getAttributes')) {
            $attributes = $reflectionClass->getAttributes();
            if ($reflectionClass->getName() === Annotation::class || empty($attributes)) {
                return;
            }

            foreach ($attributes as $attribute) {
                $myAnnotation = $attribute->newInstance();
                if ($myAnnotation instanceof \Attribute || $myAnnotation instanceof Annotation) {
                    continue;
                }

                $annotationMeta = $this->annotationParser->getAnnotationMeta($myAnnotation);
                if ($annotationMeta != null && $annotationMeta->effectiveTarget(Annotation::TARGET_CLASS)) {
                    $annotationProcessor = $this->annotationParser->getAnnotationProcessor($annotationMeta);
                    if ($annotationProcessor !== null) {
                        $annotationProcessor->handlerClazz($myAnnotation,$this->clazz);
                    }
                }
            }
        }
    }

    /**
     * 解析方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function parseMethodAnnotation():void
    {
        $reflectionMethods = $this->reflectionClass->getMethods();
        foreach ($reflectionMethods as $reflectionMethod) {
            $this->parseDoctrineMethodAnnotation($reflectionMethod);
            $this->parsePhpMethodAnnotation($reflectionMethod);
        }
    }

    protected function parseDoctrineMethodAnnotation(\ReflectionMethod $reflectionMethod):void
    {
        $annotationMethodList = $this->annotationParser->getMethodAnnotations($reflectionMethod);
        foreach ($annotationMethodList as $myAnnotation) {
            $annotationMeta = $this->annotationParser->getAnnotationMeta($myAnnotation);
            if ($annotationMeta != null && $annotationMeta->effectiveTarget(Annotation::TARGET_METHOD)) {
                $annotationProcessor = $this->annotationParser->getAnnotationProcessor($annotationMeta);
                if ($annotationProcessor !== null) {
                    $annotationProcessor->handlerMethod($myAnnotation,$this->clazz,$reflectionMethod->getName());
                }
            }
        }
    }

    protected function parsePhpMethodAnnotation(\ReflectionMethod $reflectionMethod):void
    {
        if (method_exists($reflectionMethod,'getAttributes')) {
            $attributes = $reflectionMethod->getAttributes();
            if ($reflectionMethod->getName() === Annotation::class || empty($attributes)) {
                return;
            }

            foreach ($attributes as $attribute) {
                $myAnnotation = $attribute->newInstance();
                if ($myAnnotation instanceof \Attribute || $myAnnotation instanceof Annotation) {
                    continue;
                }

                $annotationMeta = $this->annotationParser->getAnnotationMeta($myAnnotation);
                if ($annotationMeta != null && $annotationMeta->effectiveTarget(Annotation::TARGET_METHOD)) {
                    $annotationProcessor = $this->annotationParser->getAnnotationProcessor($annotationMeta);
                    if ($annotationProcessor !== null) {
                        $annotationProcessor->handlerMethod($myAnnotation,$this->clazz,$reflectionMethod->getName());
                    }
                }
            }
        }
    }



    /**
     * 解析属性注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function parseAttributeAnnotation():void
    {
        $properties = $this->reflectionClass->getProperties();
        foreach ($properties as $propertie) {
            $this->parseDoctrineAttributeAnnotation($propertie);
            $this->parsePhpAttributeAnnotation($propertie);
        }

    }

    protected function parseDoctrineAttributeAnnotation(\ReflectionProperty $propertie):void
    {
        $annotationPropertieList = $this->annotationParser->getPropertyAnnotations($propertie);
        foreach ($annotationPropertieList as $myAnnotation) {
            $annotationMeta = $this->annotationParser->getAnnotationMeta($myAnnotation);
            if ($annotationMeta != null && $annotationMeta->effectiveTarget(Annotation::TARGET_FIELD)) {
                $annotationProcessor = $this->annotationParser->getAnnotationProcessor($annotationMeta);
                if ($annotationProcessor !== null) {
                    $annotationProcessor->handlerAttribute($myAnnotation,$this->clazz,$propertie->getName());
                }
            }
        }
    }

    protected function parsePhpAttributeAnnotation(\ReflectionProperty $propertie):void
    {
        if (method_exists($propertie,'getAttributes')) {
            $attributes = $propertie->getAttributes();
            if ($propertie->getName() === Annotation::class || empty($attributes)) {
                return;
            }

            foreach ($attributes as $attribute) {
                $myAnnotation = $attribute->newInstance();
                if ($myAnnotation instanceof \Attribute || $myAnnotation instanceof Annotation) {
                    continue;
                }

                $annotationMeta = $this->annotationParser->getAnnotationMeta($myAnnotation);
                if ($annotationMeta != null && $annotationMeta->effectiveTarget(Annotation::TARGET_FIELD)) {
                    $annotationProcessor = $this->annotationParser->getAnnotationProcessor($annotationMeta);
                    if ($annotationProcessor !== null) {
                        $annotationProcessor->handlerAttribute($myAnnotation,$this->clazz,$propertie->getName());
                    }
                }
            }
        }
    }


}
