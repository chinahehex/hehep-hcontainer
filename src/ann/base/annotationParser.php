<?php
namespace hehe\core\hcontainer\ann\base;

use Doctrine\Common\Annotations\AnnotationReader;
use hehe\core\hcontainer\ann\AnnotationManager;
use ReflectionClass;

/**
 * 注解解析类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class annotationParser
{

    protected $annotationReader;

    /**
     * 注解处理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var AnnotationProcessor[]
     */
    protected $annotationProcessors;

    /**
     * 容器管理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var AnnotationManager
     */
    protected $annotationManager = null;

    public function __construct($annotationManager)
    {
        $this->annotationReader = new AnnotationReader();
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
    public function getAnnotationProcessor($annotationMeta)
    {
        $processorClass = $annotationMeta->getProcessor();
        if (!$this->annotationManager->hasProcessor($processorClass)) {
            $this->annotationManager->makeProcessor($processorClass);
        }

        return $this->annotationManager->getProcessor($processorClass);
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
    public function getAnnotationMeta($myAnnotation)
    {
        $reflectionClass = new ReflectionClass(get_class($myAnnotation));
        /** @var Annotation $annotationMeta */
        $annotationMeta = $this->annotationReader->getClassAnnotation($reflectionClass,Annotation::class);

        return $annotationMeta;
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
    public function parse($clazzList = [])
    {
        try {
            foreach ($clazzList as $clazz) {
                $annotationClass = new AnnotationClass($clazz,$this,$this->annotationReader);
                $annotationClass->parse();
            }
        } catch (\Exception $exception) {
            //var_dump($exception->getTraceAsString());
            throw $exception;
        }

    }

}
