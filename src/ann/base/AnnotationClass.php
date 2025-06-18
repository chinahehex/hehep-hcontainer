<?php
namespace hehe\core\hcontainer\ann\base;

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

    public function __construct(string $clazz,AnnotationParser $annotationParser)
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
    public function parse():void
    {
        $this->parseClassAnnotation();
        $this->parseMethodAnnotation();
        $this->parsePropertyAnnotation();
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

        $annotations = $this->annotationParser->getClassAnnotations($this->reflectionClass);

        foreach ($annotations as $annotation) {
            /**@var AnnotationProcessor $processor **/
            list($target,$myAnnotation,$processor) =  array_values($annotation);
            if ($processor != null) {
                $processor->handleClass($myAnnotation,$target);
            } else if (method_exists($myAnnotation,'handleClass')) {
                $myAnnotation->handleClass($this->clazz);
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
            $annotations = $this->annotationParser->getMethodAnnotations($reflectionMethod);
            foreach ($annotations as $annotation) {
                /** @var AnnotationProcessor $processor **/
                list($target,$myAnnotation,$processor) =  array_values($annotation);
                if ($processor != null) {
                    $processor->handleMethod($myAnnotation,$this->clazz,$target);
                } else if (method_exists($myAnnotation,'handleMethod')) {
                    $myAnnotation->handleMethod($myAnnotation,$this->clazz,$target);
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
    protected function parsePropertyAnnotation():void
    {
        $properties = $this->reflectionClass->getProperties();
        foreach ($properties as $propertie) {
            $annotations = $this->annotationParser->getPropertyAnnotations($propertie);
            foreach ($annotations as $annotation) {
                /** @var AnnotationProcessor $processor **/
                list($target,$myAnnotation,$processor) =  array_values($annotation);
                if ($processor != null) {
                    $processor->handleProperty($myAnnotation,$this->clazz,$target);
                } else if (method_exists($myAnnotation,'handleProperty')) {
                    $myAnnotation->handleProperty($myAnnotation,$this->clazz,$target);
                }
            }
        }
    }




}
