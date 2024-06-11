<?php
namespace hehe\core\hcontainer\aop;

use hehe\core\hcontainer\aop\base\AopBehavior;
use hehe\core\hcontainer\aop\base\Aspect;

/**
 * aop 管理器
 *<B>说明：</B>
 *<pre>
 *
 * 基本概念:
 *
 *  Before Advice：在方法前切入；
    After Advice：在方法后切入，抛出异常时也会切入；
    After Returning Advice：在方法返回后切入，抛出异常不会切入；
    After Throwing  Advice：在方法抛出异常时切入；
    Around Advice：在方法执行前后切入，可以中断或忽略原有流程的执行
 *
 *
 * Joinpoint：拦截点，如某个业务方法
    Pointcut：Joinpoint的表达式，表示拦截哪些方法。一个Pointcut对应多个Joinpoint
    Advice：要切入的逻辑
    Before Advice：在方法前切入
    After Advice：在方法后切入，抛出异常则不会切入
    After Returning Advice：在方法返回后切入，抛出异常则不会切入
    After Throwing Advice：在方法抛出异常时切入
    Around Advice：在方法执行前后切入，可以中断或忽略原有流程的执行
 *
 *</pre>
 *<B>示例：</B>
 *<pre>
 *  e.g 方法添加日志打印
 *
 * 定义日志行为
 * class LogHser extends AopBehavior
    {
        public function invoke($target, $method, $parameters, $returnResult, $t = null)
        {
            // TODO: Implement invoke() method.
            echo "log:" . rand(1,200) . '-';
        }
    }
 *</pre>
 */
class AopManager
{
    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  基本格式:['class'=>'拦截点配置']
     *</pre>
     * @var Aspect[]
     */
    public $aspects = [];

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  基本格式:['拦截点表达式']['拦截点方法'][通知点]
     *</pre>
     * @param AopBehavior[] $aopBehaviors
     * @param string $advice 通知点
     * @param string $pointcut 拦截点表达式,一般为正则表达式
     * @param string $clazz 类
     */
    public function addAspect($aopBehaviors = [],$advice,$pointcut,$clazz)
    {
        $clazz_pointcut = $this->buildPointcut($clazz,$pointcut);
        if (isset($this->aspects[$clazz_pointcut])) {
            $aspect = $this->aspects[$clazz_pointcut];
            $aspect->addBehavior($advice,$aopBehaviors,$pointcut);
        } else {
            $aspect = new Aspect();
            $aspect->addBehavior($advice,$aopBehaviors,$pointcut);
            $this->aspects[$clazz_pointcut] = $aspect;
        }
    }


    /**
     * 匹配拦截点有通知
     *<B>说明：</B>
     *<pre>
     *  基本格式:['拦截点表达式']['拦截点方法'][通知点]
     *</pre>
     * @param string $expression
     * @return Aspect|boolean false 表示匹配不到
     */
    public function matchAspect($clazz,$method)
    {
        $match_result = false;
        if (!isset($this->aspects[$clazz])) {
            return false;
        }

        $aspect = $this->aspects[$clazz];

        if (!$aspect->matchAspect($method)) {
            return false;
        }

        return $aspect;
    }

    /**
     * 执行切面行为
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $target 目标对象
     * @param string $method 对象方法
     * @param array $parameters 方法参数
     * @return mixed;
     */
    public function execute($target,$method, $parameters = [])
    {
        $expression =  $this->buildPointcut(get_class($target),$method);
        $aspect = $this->matchAspect($expression,$method);
        if ($aspect === false) {
            // 未找到拦截点,直接执行方法
            return call_user_func_array([$target,$method],$parameters);
        }

        return $aspect->doAdvice($target,$method,$parameters);
    }

    protected function buildPointcut($clazz,$method)
    {
       return str_replace('\\','.',$clazz);
    }

}
