<?php
namespace hehe\core\hcontainer\annotation;

use hehe\core\hcontainer\ann\base\AnnotationProcessor;
use hehe\core\hcontainer\base\Definition;

/**
 * Bean注解处理器
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class BeanProcessor extends AnnotationProcessor
{
    // Bean定义列表
    protected $beanDefinitions = [];

    // 自定义注解处理方法
    protected $annotationHandlers = [
        'Ref'=>'handleRefAnnotation'
    ];

    protected function appendDefinition(array $bean_conf,string $class):void
    {
        if (isset($this->beanDefinitions[$class])) {
            $this->beanDefinitions[$class] = array_merge($this->beanDefinitions[$class],$bean_conf);
        } else {
            $this->beanDefinitions[$class] = $bean_conf;
        }
    }

    public function annotationToBeanConfig(array $annAttributes,string $class):array
    {
        $beanId = '';
        if (!empty($annAttributes[Definition::SYS_ATTR_ID])) {
            $beanId = $annAttributes[Definition::SYS_ATTR_ID];
        } else {
            if (isset($this->beanDefinitions[$class][Definition::SYS_ATTR_ID])) {
                $beanId = $this->beanDefinitions[$class][Definition::SYS_ATTR_ID];
            }
        }

        if (empty($beanId)) {
            $beanId = $class;
        }

        $annAttributes[Definition::SYS_ATTR_ID] = $beanId;
        $annAttributes['class'] = $class;

        $bean_conf = [];
        foreach ($annAttributes as $index=>$val) {
            $bean_conf[$index] = $val;
        }

        return $bean_conf;
    }

    public function handleAnnotationClass($annotation,string $class):void
    {
        $annAttributes = $this->getProperty($annotation);
        $bean_conf = $this->annotationToBeanConfig($annAttributes,$class);
        $this->appendDefinition($bean_conf,$class);
    }

    /**
     * 处理Ref 注解
     * @param $annotation
     * @param string $class
     * @param string $property
     */
    public function handleRefAnnotation($annotation,string $class,string $property):void
    {
        $annAttributes = [
            $property=>$annotation,
        ];

        $bean_conf = $this->annotationToBeanConfig($annAttributes,$class);
        $this->appendDefinition($bean_conf,$class);
    }


    public function handleProcessorFinish():void
    {
        $beanDefinitionList = [];
        foreach ($this->beanDefinitions as $beanDefinition) {
            $beanDefinitionList[$beanDefinition[Definition::SYS_ATTR_ID]] = $beanDefinition;
        }

        $this->getContainerManager()->batchRegister($beanDefinitionList);

        $this->beanDefinitions = [];
    }

}
