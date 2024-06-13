<?php
namespace hehe\core\hcontainer\aop\annotation;


use hehe\core\hcontainer\ann\base\AnnotationProcessor;
use hehe\core\hcontainer\aop\AopManager;
use hehe\core\hcontainer\aop\base\AopProxyHandler;
use hehe\core\hcontainer\aop\base\Aspect;
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
     *  基本格式:['类名'=>[['拦截点表达式','通知点位置','行为列表']]]
     *</pre>
     * @var array
     */
    protected $aspects = [];

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $annotation
     * @param string $clazz
     */
    public function annotationHandlerClazz($annotation,$clazz)
    {
        $values = $this->getAttribute($annotation);

        $this->addAspect($clazz,'',$values);
    }

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $annotation
     * @param string $clazz
     * @param string $method
     */
    public function annotationHandlerMethod($annotation,$clazz,$method)
    {
        $values = $this->getAttribute($annotation);

        $this->addAspect($clazz,$method,$values);
    }


    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  基本格式:['拦截点表达式']['拦截点方法'][通知点]
     *</pre>
     * @param string $clazz
     * @param string $method
     * @param array $values
     */
    public function addAspect($clazz,$method,$values)
    {
        if (!empty($method)) {
            $this->aspects[$clazz][] = [$method,$values['advice'],$values['behaviors']];
        } else {
            $this->aspects[$clazz][] = [$values['pointcut'],$values['advice'],$values['behaviors']];
        }
    }

    public function endScanHandle()
    {

        $hcontainer = $this->getContainerManager();
        $aopManager = $hcontainer->getAopManager();
        foreach ($this->aspects as $clazz=>$aspects) {
            foreach ($aspects as $aspect) {
                list($pointcut,$advice,$behaviors) = $aspect;
                $aopManager->addAspect($clazz,$pointcut,$advice,$behaviors);
            }

            // 更新bean代理状态
            $beanId = $hcontainer->getBeanId($clazz);
            $hcontainer->appendComponent($beanId,[
                Definition::SYS_ATTR_ONPROXY=>true,
                Definition::SYS_ATTR_PROXYHANDLER=>AopProxyHandler::class,
                Definition::SYS_ATTR_CLASS=>$clazz,
            ]);
        }

        $this->aspects = [];
    }
}
