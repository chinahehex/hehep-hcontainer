<?php
namespace hehe\core\hcontainer\aop\annotation;


use hehe\core\hcontainer\ann\base\AnnotationProcessor;
use hehe\core\hcontainer\aop\AopManager;
use hehe\core\hcontainer\base\Definition;

/**
 * 切面注解处理器
 *<B>说明：</B>
 *<pre>
 * 基本概念:
 *</pre>
 */
class AdviceProcessor extends AnnotationProcessor
{

    /**
     * 切面集合
     *<B>说明：</B>
     *<pre>
     *  基本格式:[['类名','拦截点表达式','通知点位置','行为列表']]
     *</pre>
     * @var array
     */
    protected $aspects = [];

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  基本格式:['拦截点表达式']['拦截点方法'][通知点]
     *</pre>
     * @param string $class
     * @param string $method
     * @param array $annAttributes
     */
    public function addAspect(string $class,string $method,array $annAttributes):void
    {
        if (!empty($method)) {
            $this->aspects[] = [$class,$method,$annAttributes['advice'],$annAttributes['behaviors']];
        } else {
            $this->aspects[] = [$class,$annAttributes['pointcut'],$annAttributes['advice'],$annAttributes['behaviors']];
        }
    }

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $annotation
     * @param string $class
     */
    public function handleAnnotationClass($annotation,string $class):void
    {
        $annAttributes = $this->getProperty($annotation);

        $this->addAspect($class,'',$annAttributes);
    }

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $annotation
     * @param string $class
     * @param string $method
     */
    public function handleAnnotationMethod($annotation,string $class,string $method):void
    {
        $annAttributes = $this->getProperty($annotation);

        $this->addAspect($class,$method,$annAttributes);
    }

    public function handleProcessorFinish()
    {

        $hcontainer = $this->getContainerManager();
        $aopManager = $hcontainer->getAopManager();
        foreach ($this->aspects as $class=>$aspect) {
            list($class,$pointcut,$advice,$behaviors) = $aspect;
            $aopManager->addAspect($class,$pointcut,$advice,$behaviors);

            // 更新bean代理状态
            $beanId = $hcontainer->getBeanId($class);
            $hcontainer->appendComponent($beanId,[
                Definition::SYS_ATTR_ONPROXY=>true,
                Definition::SYS_ATTR_CLASS=>$class,
            ]);
        }

        $this->aspects = [];
    }
}
