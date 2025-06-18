<?php
namespace hehe\core\hcontainer\ann\base;

use Doctrine\Common\Annotations\AnnotationReader;
use hehe\core\hcontainer\ann\ScanManager;
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
class AnnotationParserbak
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
     * @var ScanManager
     */
    protected $scanManager;

    public function __construct(ScanManager $scanManager)
    {
        if (class_exists(AnnotationReader::class)) {
            $this->annotationReader = new AnnotationReader();
        }

        $this->scanManager = $scanManager;
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

        return $this->scanManager->getProcessor($processorClass);
    }

    /**
     * 查找指定某注解
     * @param string $reflectionClass
     * @param string $annotation
     */
    public function findClassAnnotation(ReflectionClass $reflectionClass, string $annotation)
    {
        $classAnnotations  = $this->annotationReader->getClassAnnotations($reflectionClass);
        $targetAnnotation = null;
        foreach ($classAnnotations as $myAnnotation) {
            if ($myAnnotation instanceof $annotation) {
                $targetAnnotation = $myAnnotation;
            }
        }

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
            }
        }



        return $targetAnnotation;
    }

    public function getClassAnnotations(ReflectionClass $reflectionClass):array
    {
        $annotations = [];

        $annotations = array_merge($annotations,$this->getDoctrineClassAnnotations($reflectionClass));
        $annotations = array_merge($annotations,$this->getPhpClassAnnotations($reflectionClass));

        return $annotations;
    }

    public function getMethodAnnotations(ReflectionMethod $reflectionMethod):array
    {
        $annotations = [];

        $annotations = array_merge($annotations,$this->getDoctrineMethodAnnotations($reflectionMethod));
        $annotations = array_merge($annotations,$this->getPhpMethodAnnotations($reflectionMethod));

        return $annotations;
    }

    public function getPropertyAnnotations(ReflectionProperty $propertie):array
    {
        $annotations = [];

        $annotations = array_merge($annotations,$this->getDoctrinePropertyAnnotations($propertie));
        $annotations = array_merge($annotations,$this->getPhpPropertyAnnotations($propertie));

        return $annotations;
    }

    public function getDoctrineClassAnnotations(ReflectionClass $reflectionClass):array
    {
        $annotations = [];

        if (is_null($this->annotationReader)) {
            return $annotations;
        }

        $classAnnotations  = $this->annotationReader->getClassAnnotations($reflectionClass);
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

    public function getDoctrineMethodAnnotations(ReflectionMethod $reflectionMethod)
    {
        $annotations = [];

        if (is_null($this->annotationReader)) {
            return $annotations;
        }

        $annotationMethodList = $this->annotationReader->getMethodAnnotations($reflectionMethod);
        foreach ($annotationMethodList as $myAnnotation) {
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

    public function getDoctrinePropertyAnnotations(ReflectionProperty $propertie):array
    {
        $annotations = [];

        if (is_null($this->annotationReader)) {
            return $annotations;
        }

        $annotationPropertieList = $this->annotationReader->getPropertyAnnotations($propertie);
        foreach ($annotationPropertieList as $myAnnotation) {
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

    protected function getPhpClassAnnotations(ReflectionClass $reflectionClass):array
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

                $annotationProcessor = null;

                // 判断目标
                $annotationMeta = $this->getAnnotationMeta($myAnnotation);
                if ($annotationMeta != null) {
                    if (!$annotationMeta->effectiveTarget(Annotation::TARGET_CLASS)) {
                        continue;
                    }
                    $annotationProcessor = $this->getAnnotationProcessor($annotationMeta);
                }

                $annotations[] = ['target'=>$reflectionClass->getName(),'annotation'=>$myAnnotation,'processor'=>$annotationProcessor];
            }
        }

        return $annotations;
    }

    protected function getPhpMethodAnnotations(\ReflectionMethod $reflectionMethod):array
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

                $annotationProcessor = null;

                $annotationMeta = $this->getAnnotationMeta($myAnnotation);
                if ($annotationMeta != null ) {
                    if (!$annotationMeta->effectiveTarget(Annotation::TARGET_METHOD)) {
                        continue;
                    }

                    $annotationProcessor = $this->getAnnotationProcessor($annotationMeta);
                }

                $annotations[] = ['target'=>$reflectionMethod->getName(),'annotation'=>$myAnnotation,'processor'=>$annotationProcessor];
            }
        }

        return $annotations;
    }

    protected function getPhpPropertyAnnotations(\ReflectionProperty $propertie):array
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
        }

        return $annotations;
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
