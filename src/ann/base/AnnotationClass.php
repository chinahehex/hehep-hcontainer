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

    protected $annotationReader;

    /**
     * 注解解析类
     * @var AnnotationParser
     */
    protected $annotationParser;

    /**
     * 注解处理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var AnnotationProcessor[]
     */
    protected static $annotationProcessors;

    public function __construct($clazz,annotationParser $annotationParser,AnnotationReader $annotationReader)
    {
        $this->clazz = $clazz;
        $this->annotationReader = $annotationReader;
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
    protected function parseClassAnnotation()
    {
        $classAnnotations  = $this->annotationReader->getClassAnnotations($this->reflectionClass);
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

    /**
     * 解析方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function parseMethodAnnotation()
    {
        $reflectionMethods = $this->reflectionClass->getMethods();

        foreach ($reflectionMethods as $reflectionMethod) {

            $annotationMethodList = $this->annotationReader->getMethodAnnotations($reflectionMethod);
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

    }

    /**
     * 解析属性注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function parseAttributeAnnotation()
    {
        $properties = $this->reflectionClass->getProperties();
        foreach ($properties as $propertie) {
            $annotationPropertieList = $this->annotationReader->getPropertyAnnotations($propertie);
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

    }


}
