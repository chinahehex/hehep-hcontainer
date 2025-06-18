<?php
namespace hehe\core\hcontainer\ann\base;

use hehe\core\hcontainer\ann\AnnManager;
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
class AnnotationParser
{

    /**
     * @var DoctrineAnnotationParser
     */
    protected $doctrineAnnotationParser;

    /**
     * @var PhpAnnotationParser
     */
    protected $phpAnnotationParser;


    /**
     * 容器管理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var AnnManager
     */
    protected $annManager;

    public function __construct(AnnManager $annManager)
    {
        $this->doctrineAnnotationParser = new DoctrineAnnotationParser();
        $this->phpAnnotationParser = new PhpAnnotationParser();
        $this->annManager = $annManager;
    }

    public function findClassAnnotations(string $class, string $targetAnnotation):array
    {
        $annotations = [];
        $annotations = $this->phpAnnotationParser->findClassAnnotations($class,$targetAnnotation);
        $annotations = array_merge($annotations,$this->doctrineAnnotationParser->findClassAnnotations($class,$targetAnnotation));

        return $annotations;
    }

    public function findMethodAnnotations(string $class, string $method, string $targetAnnotation):array
    {
        $annotations = [];
        $annotations = $this->phpAnnotationParser->findMethodAnnotations($class,$method,$targetAnnotation);
        $annotations = array_merge($annotations,$this->doctrineAnnotationParser->findMethodAnnotations($class,$method,$targetAnnotation));

        return $annotations;
    }

    public function findPropertyAnnotations(string $class, string $property, string $targetAnnotation):array
    {
        $annotations = [];
        $annotations = $this->phpAnnotationParser->findPropertyAnnotations($class,$property,$targetAnnotation);
        $annotations = array_merge($annotations,$this->doctrineAnnotationParser->findPropertyAnnotations($class,$property,$targetAnnotation));

        return $annotations;
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

        return $this->annManager->getProcessor($processorClass);
    }

    public function getClassAnnotations(ReflectionClass $reflectionClass):array
    {
        $annotations = [];
        $annotations = $this->phpAnnotationParser->getClassAnnotations($reflectionClass);
        $annotations = array_merge($annotations,$this->doctrineAnnotationParser->getClassAnnotations($reflectionClass));
        $annotations = $this->formatClassAnnotations($reflectionClass,$annotations);

        return $annotations;
    }

    public function getMethodAnnotations(ReflectionMethod $reflectionMethod):array
    {
        $annotations = [];
        $annotations = $this->phpAnnotationParser->getMethodAnnotations($reflectionMethod);
        $annotations = array_merge($annotations,$this->doctrineAnnotationParser->getMethodAnnotations($reflectionMethod));
        $annotations = $this->formatMethodAnnotations($reflectionMethod,$annotations);

        return $annotations;
    }

    public function getPropertyAnnotations(ReflectionProperty $propertie):array
    {
        $annotations = [];
        $annotations = $this->phpAnnotationParser->getPropertyAnnotations($propertie);
        $annotations = array_merge($annotations,$this->doctrineAnnotationParser->getPropertyAnnotations($propertie));
        $annotations = $this->formatPropertyAnnotations($propertie,$annotations);

        return $annotations;
    }

    protected function formatClassAnnotations(ReflectionClass $reflectionClass,array $classAnnotations = []):array
    {
        $annotations = [];
        foreach ($classAnnotations as $myAnnotation) {
            $annotationProcessor = null;
            $annotationMeta = $this->getAnnotationMeta($myAnnotation);
            if ($annotationMeta != null) {
                if (!$annotationMeta->effectiveTarget(Annotation::TARGET_CLASS)) {
                    continue;
                }
                $annotationProcessor = $this->getAnnotationProcessor($annotationMeta);
            }

            $annotations[] = ['target'=>$reflectionClass->getName(),'annotation'=>$myAnnotation,'processor'=>$annotationProcessor];
        }

        return $annotations;
    }

    protected function formatMethodAnnotations(ReflectionMethod $reflectionMethod,array $methodAnnotations = []):array
    {
        $annotations = [];
        foreach ($methodAnnotations as $myAnnotation) {
            $annotationProcessor = null;
            $annotationMeta = $this->getAnnotationMeta($myAnnotation);
            if ($annotationMeta != null) {
                if (!$annotationMeta->effectiveTarget(Annotation::TARGET_METHOD)) {
                    continue;
                }
                $annotationProcessor = $this->getAnnotationProcessor($annotationMeta);
            }

            $annotations[] = ['target'=>$reflectionMethod->getName(),'annotation'=>$myAnnotation,'processor'=>$annotationProcessor];
        }

        return $annotations;
    }

    protected function formatPropertyAnnotations(ReflectionProperty $propertie,array $propertyAnnotations = []):array
    {
        $annotations = [];
        foreach ($propertyAnnotations as $myAnnotation) {
            $annotationProcessor = null;
            $annotationMeta = $this->getAnnotationMeta($myAnnotation);
            if ($annotationMeta != null) {
                if (!$annotationMeta->effectiveTarget(Annotation::TARGET_PROPERTY)) {
                    continue;
                }
                $annotationProcessor = $this->getAnnotationProcessor($annotationMeta);
            }

            $annotations[] = ['target'=>$propertie->getName(),'annotation'=>$myAnnotation,'processor'=>$annotationProcessor];
        }

        return $annotations;
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
        $annotationMeta = $this->phpAnnotationParser->getAnnotationMeta($myAnnotation);
        if ($annotationMeta === null) {
            $annotationMeta = $this->doctrineAnnotationParser->getAnnotationMeta($myAnnotation);
        }

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
            $annotationClass = new AnnotationClass($clazz,$this);
            $annotationClass->parse();
        }
    }

}
