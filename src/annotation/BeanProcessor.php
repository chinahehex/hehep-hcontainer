<?php
namespace hehe\core\hcontainer\annotation;

use hehe\core\hcontainer\ann\base\AnnotationProcessor;

/**
 * Bean注解处理器
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class BeanProcessor extends AnnotationProcessor
{
    // bean 定义列表
    protected $beanDefinitionList = [];

    protected $annotationHandlerMap = [
        'Ref'=>'refHandler'
    ];

    public function formatBeanAnnotationValues($annotationValues,$clazz)
    {

        if (!empty($annotationValues['id'])) {
            $beanId = $annotationValues['id'];
        } else {
            if (isset($this->beanDefinitionList[$clazz])) {
                if (!empty($this->beanDefinitionList[$clazz]['id'])) {
                    $beanId = $this->beanDefinitionList[$clazz]['id'];
                }
            }
        }

        if (empty($beanId)) {
            $beanId = $clazz;
        }

        $annotationValues['id'] = $beanId;
        $annotationValues['class'] = $clazz;

        return $this->formatBeanDefinition($annotationValues);
    }

    public function annotationHandlerClazz($annotation,$clazz)
    {
        $annotationValues = $this->getAttribute($annotation);
        $beanValues = $this->formatBeanAnnotationValues($annotationValues,$clazz);
        $this->appendDefinition($beanValues,$clazz);
    }

    protected function appendDefinition($beanValues,$clazz)
    {
        if (isset($this->beanDefinitionList[$clazz])) {
            $this->beanDefinitionList[$clazz] = array_merge($this->beanDefinitionList[$clazz],$beanValues);
        } else {
            $this->beanDefinitionList[$clazz] = $beanValues;
        }
    }


    public function refHandler($annotation,$clazz,$attribute)
    {

        //$annotationValues = $this->getAttribute($annotation);
        $attributeValues = [
            $attribute=>$annotation,
        ];

        $beanValues = $this->formatBeanAnnotationValues($attributeValues,$clazz);
        $this->appendDefinition($beanValues,$clazz);
    }

    protected function formatBeanDefinition($annotationValues)
    {
        $beanDefinition = [];
        foreach ($annotationValues as $name=>$value) {
            $beanDefinition[$name] = $value;
        }

        return $beanDefinition;
    }

    public function endScanHandle()
    {
        $beanDefinitionList = [];
        foreach ($this->beanDefinitionList as $beanDefinition) {
            $beanDefinitionList[$beanDefinition['id']] = $beanDefinition;
        }

        $this->getContainerManager()->batchRegister($beanDefinitionList);
    }

}
