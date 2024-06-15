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
    public function getAnnotationProcessor(Annotation $annotationMeta):AnnotationProcessor
    {
        $processorClass = $annotationMeta->getProcessor();

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
    public function getAnnotationMeta($myAnnotation):?Annotation
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
    public function parse(array $clazzList = []):void
    {
        foreach ($clazzList as $clazz) {
            $annotationClass = new AnnotationClass($clazz,$this,$this->annotationReader);
            $annotationClass->parse();
        }
    }

}
