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
     * 注解标签列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $classAnnotations = [];

    protected $annotationHandlerMap = [];

    /**
     * 所有注解器对象
     *<B>说明：</B>
     *<pre>
     *  格式:['类名']['注解类型'][]
     *</pre>
     * @var array
     */
    protected $annotationsors = [];

    protected $ann_dict = [];

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
     * 获取注解标签列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return array
     */
    public function getClassAnnotations():array
    {
        return $this->classAnnotations;
    }

    /**
     * 收集类注解
     * @param object $annotation 注解类
     * @param string $clazz 类路径
     */
    protected function collectClass($annotation,string $clazz):void
    {
        $this->annotationsors[$clazz]['class'][$clazz][] = $annotation;
        $this->ann_dict[get_class($annotation)][] = ['class'=>$clazz,'target_type'=>'class','target'=>$clazz,'annotation'=>$annotation];
    }

    /**
     * 收集类属性注解
     * @param object $annotation 注解类
     * @param string $clazz 类路径
     * @param string $attribute 属性名
     */
    protected function collectAttribute($annotation,string $clazz,string $attribute):void
    {
        $this->annotationsors[$clazz]['attribute'][$attribute][] = $annotation;
        $this->ann_dict[get_class($annotation)][] = ['class'=>$clazz,'target_type'=>'attribute','target'=>$attribute,'annotation'=>$annotation];
    }

    /**
     * 收集类方法注解
     * @param object $annotation 注解类
     * @param string $clazz 类路径
     * @param string $method 类方法
     */
    protected function collectMethod($annotation,string $clazz,string $method):void
    {
        $this->annotationsors[$clazz]['method'][$method][] = $annotation;

        $this->ann_dict[get_class($annotation)][] = ['class'=>$clazz,'target_type'=>'method','target'=>$method,'annotation'=>$annotation];
    }

    /**
     * 获取解析后的注解数据
     * @param string $class_key
     * @return array
     */
    public function getAnnotationors(string $class_key = '')
    {
        $name = '';
        $target = '';
        if (strpos($class_key,'@') !== false) {
            $class_arr = explode('@',$class_key);
            if (count($class_arr) == 3) {
                list($class,$target,$name) = $class_arr;
            } else if (count($class_arr) == 2) {
                list($class,$target) = $class_arr;
            }
        } else {
            $class = $class_key;
        }

        if (!empty($class)) {
            if ($target == 'class') {
                return $this->annotationsors[$class]['class'][$class];
            } else if ($target == 'attribute') {
                return $this->annotationsors[$class]['attribute'][$name];
            } else if ($target == 'method') {
                return $this->annotationsors[$class]['method'][$name];
            } else {
                return $this->annotationsors[$class];
            }
        } else {
            return $this->annotationsors;
        }
    }

    /**
     * 获取收集到的注解集合
     *
     * 如未设置查找条件,则返回所有注解
     *
     * @param string $ann_class 查找的注解类路径
     * @param string $target_type 查找的类型,比如class,类型,attribute类型,method 方法类型
     * @param string $target 具体的类型对应的值,比如查找某个方法"doaction"
     * @return array<class="被注解的类",'target_type'=>'注解作用域(class,method)','target'=>'注解类型值('add')','annotation'=>'注解对象'>
     */
    public function getAnns(string $ann_class = '',string $target_type = '',string $target = ''):array
    {
        if (empty($ann_class)) {
            return $this->ann_dict;
        }

        if (!isset($this->ann_dict[$ann_class])) {
            return [];
        }

        if (empty($target_type) || empty($target)) {
            return $this->ann_dict[$ann_class];
        }

        $ann_list = [];

        foreach ($this->ann_dict[$ann_class] as $ann) {
            if (!empty($target_type) && $ann['target_type'] != $target_type) {
                continue;
            }

            if (!empty($target) && $ann['target'] != $target) {
                continue;
            }

            $ann_list[] = $ann;
        }

        return $ann_list;
    }

    /**
     * 获取注解类简短名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param BaseAnnotation $annotation
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
     * @param BaseAnnotation $annotation
     * @param string $clazz
     */
    public function handlerClazz($annotation,$clazz)
    {
        $shortName = $this->getAnnotationShortName($annotation);
        $annotationHandlerMethod = 'annotationHandlerClazz';
        if (isset($this->annotationHandlerMap[$shortName])) {
            $annotationHandlerMethod = $this->annotationHandlerMap[$shortName];
        }

        if (method_exists($this,$annotationHandlerMethod)) {
            $this->$annotationHandlerMethod($annotation,$clazz);
        } else {
            $this->collectClass($annotation,$clazz);
        }
    }

    /**
     * 处理属性注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param BaseAnnotation $annotation
     * @param string $clazz
     * @param string $attribute
     */
    public function handlerAttribute($annotation,string $clazz,string $attribute)
    {
        $shortName = $this->getAnnotationShortName($annotation);
        $annotationHandlerMethod = 'annotationHandlerAttribute';
        if (isset($this->annotationHandlerMap[$shortName])) {
            $annotationHandlerMethod = $this->annotationHandlerMap[$shortName];
        }

        if (method_exists($this,$annotationHandlerMethod)) {
            $this->$annotationHandlerMethod($annotation,$clazz,$attribute);
        } else {
            $this->collectAttribute($annotation,$clazz,$attribute);
        }
    }

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param BaseAnnotation $annotation
     * @param string $clazz
     * @param string $method
     */
    public function handlerMethod($annotation,string $clazz,string $method)
    {
        $shortName = $this->getAnnotationShortName($annotation);
        $annotationHandlerMethod = 'annotationHandlerMethod';
        if (isset($this->annotationHandlerMap[$shortName])) {
            $annotationHandlerMethod = $this->annotationHandlerMap[$shortName];
        }

        if (method_exists($this,$annotationHandlerMethod)) {
            $this->$annotationHandlerMethod($annotation,$clazz,$method);
        } else {
            $this->collectMethod($annotation,$clazz,$method);
        }
    }

    /**
     * 获取注解的所有属性值
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $annotation
     * @param string $class
     * @param string $method
     * @param string $attribute
     * @return array
     */
    protected function getAttribute($annotation,string $class = '',string $method = '',string $attribute = '')
    {
        $values = [];

        if (method_exists($annotation,'formatData')) {
            $values = call_user_func_array([$annotation,'formatData'],[$class,$method,$attribute]);
        } else {
            $class = new ReflectionClass(get_class($annotation));
            foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (!$property->isStatic()) {
                    $propertieName = $property->getName();
                    $values[$propertieName] = $annotation->$propertieName;
                }
            }
        }

        return $values;
    }

    public function triggerEndScan()
    {

        $this->endScanHandle();

        // 清空资源
        $this->annotationsors = [];
    }

    // 接触扫描处理
    public function endScanHandle()
    {

    }

}
