<?php
namespace hehe\core\hcontainer\ann\base;

use hehe\core\hcontainer\ContainerManager;
use ReflectionClass;

/**
 * 注解处理器基类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class AnnotationProcessor
{
    /**
     * 容器管理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var ContainerManager
     */
    protected $containerManager;

    /**
     * 自定义注解处理方法
     * @var array
     */
    protected $annotationHandlers = [];

    /**
     * 所有注解器对象
     *<B>说明：</B>
     *<pre>
     *  格式:['类名'][[]
     *</pre>
     * @var array
     */
    protected $annotationsors = [];

    /**
     * 注解处理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var AnnotationProcessor[]
     */
    protected $annotationProcessors = [];

    public function __construct(ContainerManager $containerManager)
    {
        $this->containerManager = $containerManager;
    }

    public function getContainerManager():ContainerManager
    {
        return $this->containerManager;
    }


    /**
     * 收集类注解
     * @param object $annotation 注解类
     * @param string $clazz 类路径
     */
    protected function collectClass($annotation,string $class):void
    {
        $this->annotationsors[$class][] = ['class'=>$class,'target'=>$class,'type'=>'class','annotation'=> $annotation];
    }

    /**
     * 收集类属性注解
     * @param object $annotation 注解类
     * @param string $clazz 类路径
     * @param string $property 属性名
     */
    protected function collectProperty($annotation,string $class,string $property):void
    {
        $this->annotationsors[$class][] = ['class'=>$class,'target'=>$property,'type'=>'property','annotation'=> $annotation];
    }

    /**
     * 收集类方法注解
     * @param object $annotation 注解类
     * @param string $clazz 类路径
     * @param string $method 类方法
     */
    protected function collectMethod($annotation,string $class,string $method):void
    {
        $this->annotationsors[$class][] = ['class'=>$class,'target'=>$method,'type'=>'method','annotation'=> $annotation];
    }

    /**
     * 获取解析后的注解数据
     * @param string $keyword
     * @param string $annotation
     * @return array
     */
    public function getAnnotationors(string $keyword = '',string $annotation = null)
    {
        $name = '';
        $target_type = '';
        if (strpos($keyword,'@@') !== false) {// 属性
            $target_type = 'property';
            list($class, $target) = explode('@@',$keyword);
        } else if (strpos($keyword,'@') !== false) {// 方法
            list($class, $target) = explode('@',$keyword);
            $target_type = 'method';
        } else {// 类
            $target_type = 'class';
            $class = $keyword;
            $target = $keyword;
        }

        $annotationList = [];
        if (isset($this->annotationsors[$class])) {
            $annotationList = $this->annotationsors[$class];
        } else {
            $annotationList = array_values($this->annotationsors);
        }

        if (empty($annotationList)) {
            return [];
        }

        $annList = [];
        foreach ($annotationList as $ann_arr) {
            list($ann_class,$ann_target,$ann_type,$ann) = array_values($ann_arr);
            if (!empty($target_type) && $target_type != $ann_type) {
                continue;
            }

            if (!empty($target) && $target != $ann_target) {
                continue;
            }

            if (!empty($annotation) && !($ann instanceof $annotation)) {
                continue;
            }

            $annList[] = $ann;

        }

        return $annList;
    }

    /**
     * 获取注解类简短名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Ann $annotation
     * @return string
     */
    protected function getAnnotationShortName($annotation)
    {
        return basename(str_replace('\\', '/', get_class($annotation)));
    }

    /**
     * 处理类注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Ann $annotation
     * @param string $class
     */
    public function handleClass($annotation,string $class)
    {

        $shortName = $this->getAnnotationShortName($annotation);
        if (isset($this->annotationHandlers[$shortName])) {
            $handler = $this->annotationHandlers[$shortName];
            $this->{$handler}($annotation,$class,$class,'class');
        } else if (method_exists($this,'handleAnnotationClass')) {
            $this->handleAnnotationClass($annotation,$class);
        } else if (method_exists($annotation,'handleAnnotation')) {
            $this->handleAnnotation($annotation,$class,$class,'class');
        }
    }

    /**
     * 处理属性注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Ann $annotation
     * @param string $class
     * @param string $attribute
     */
    public function handleProperty($annotation,string $class,string $attribute)
    {

        $shortName = $this->getAnnotationShortName($annotation);
        if (isset($this->annotationHandlers[$shortName])) {
            $handler = $this->annotationHandlers[$shortName];
            $this->{$handler}($annotation,$class,$attribute,'property');
        } else if (method_exists($this,'handleAnnotationProperty')) {
            $this->handleAnnotationProperty($annotation,$class,$attribute);
        } else if (method_exists($annotation,'handleAnnotation')) {
            $this->handleAnnotation($annotation,$class,$attribute,'property');
        }
    }

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Ann $annotation
     * @param string $class
     * @param string $method
     */
    public function handleMethod($annotation,string $class,string $method)
    {
        $shortName = $this->getAnnotationShortName($annotation);
        if (isset($this->annotationHandlers[$shortName])) {
            $handler = $this->annotationHandlers[$shortName];
            $this->{$handler}($annotation,$class,$method,'method');
        } else if (method_exists($this,'handleAnnotationMethod')) {
            $this->handleAnnotationMethod($annotation,$class,$method);
        } else if (method_exists($annotation,'handleAnnotation')) {
            $this->handleAnnotation($annotation,$class,$method,'method');
        }
    }

    /**
     * 获取注解的所有属性值
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $annotation
     * @return array
     */
    protected function getAttribute($annotation)
    {
        $annAttributes = [];

        $annAttributes = get_object_vars($annotation);

        return $annAttributes;
    }

    /**
     * 获取注解的所有属性值
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $annotation
     * @return array
     */
    protected function getProperty($annotation)
    {
        $annAttributes = [];

        $class = new ReflectionClass(get_class($annotation));
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $propertieName = $property->getName();
                $annAttributes[$propertieName] = $annotation->$propertieName;
            }
        }

        return $annAttributes;
    }

    public function handleProcessor()
    {

        $this->handleProcessorFinish();

        // 清空资源
        $this->annotationsors = [];
    }

    // 接触扫描处理
    public function handleProcessorFinish()
    {

    }

}
