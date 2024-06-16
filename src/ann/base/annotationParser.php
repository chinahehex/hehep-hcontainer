<?php
namespace hehe\core\hcontainer\ann\base;

use Doctrine\Common\Annotations\AnnotationReader;
use hehe\core\hcontainer\ann\AnnotationManager;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
/**
 * 注解解析类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class annotationParser
{

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;


    /**
     * 容器管理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var AnnotationManager
     */
    protected $annotationManager;

    public function __construct(AnnotationManager $annotationManager)
    {
        if (class_exists(AnnotationReader::class)) {
            $this->annotationReader = new AnnotationReader();
        }

        $this->annotationManager = $annotationManager;
    }

    /**
     * 获取注解处理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Annotation $annotationMeta
     * @return AnnotationProcessor
     */
    public function getAnnotationProcessor(Annotation $annotationMeta):AnnotationProcessor
    {
        $processorClass = $annotationMeta->getProcessor();

        return $this->annotationManager->getProcessor($processorClass);
    }

    public function getClassAnnotations(ReflectionClass $reflectionClass)
    {
        $classAnnotations = [];
        if (!is_null($this->annotationReader)) {
            $classAnnotations  = $this->annotationReader->getClassAnnotations($reflectionClass);
        }

        return $classAnnotations;
    }

    public function getMethodAnnotations(ReflectionMethod $reflectionMethod)
    {

        $methodAnnotations = [];
        if (!is_null($this->annotationReader)) {
            $methodAnnotations  = $this->annotationReader->getMethodAnnotations($reflectionMethod);
        }

        return $methodAnnotations;
    }

    public function getPropertyAnnotations(ReflectionProperty $propertie)
    {
        $propertieAnnotations = [];
        if (!is_null($this->annotationReader)) {
            $propertieAnnotations  = $this->annotationReader->getPropertyAnnotations($propertie);
        }

        return $propertieAnnotations;
    }

    /**
     * 获取元注解Annotation
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param null $myAnnotation
     * @return Annotation
     */
    public function getAnnotationMeta($myAnnotation):?Annotation
    {
        $reflectionClass = new ReflectionClass(get_class($myAnnotation));
        $annotationMeta = null;
        /** @var Annotation $annotationMeta */
        if (!is_null($this->annotationReader)) {
            $annotationMeta = $this->annotationReader->getClassAnnotation($reflectionClass,Annotation::class);
        }

        if ($annotationMeta == null) {
            $annotationMeta = $this->getPhpAnnotationMeta($reflectionClass,Annotation::class);
        }

        return $annotationMeta;
    }

    protected function getPhpAnnotationMeta(ReflectionClass $reflectionClass,$annotationName)
    {
        if ($reflectionClass->getName() == Annotation::class) {
            return null;
        }

        if (method_exists($reflectionClass, 'getAttributes')) {
            $attributes = $reflectionClass->getAttributes();
            foreach ($attributes as $attribute) {
                $annotation = $attribute->newInstance();
                if ($annotation instanceof $annotationName) {
                    return $annotation;
                }
            }
        }

        return null;
    }

    /**
     * 解析php类文件,从中分离出注解信息
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $clazzList 文件列表
     * @return void
     */
    public function parse(array $clazzList = []):void
    {
        foreach ($clazzList as $clazz) {
            $annotationClass = new AnnotationClass($clazz,$this);
            $annotationClass->parse();
        }
    }

}
