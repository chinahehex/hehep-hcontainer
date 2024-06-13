<?php
namespace hehe\core\hcontainer\aop;

use hehe\core\hcontainer\aop\base\AopBehavior;
use hehe\core\hcontainer\aop\base\Aspect;

/**
 * aop 管理器
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class AopManager
{
    /**
     * Aspect集合
     *<B>说明：</B>
     *<pre>
     *  基本格式:['class'=>'拦截点配置']
     *</pre>
     * @var Aspect[]
     */
    public $aspects = [];

    /**
     * 添加切面
     *<B>说明：</B>
     *<pre>
     *  基本格式:['拦截点表达式']['拦截点方法'][通知点]
     *</pre>
     * @param string $clazz 目标类
     * @param string $pointcut 拦截点表达式,拦截的方法或正则表达式
     * @param string $advice 通知点
     * @param AopBehavior[] $aopBehaviors 切入行为
     */
    public function addAspect(string $clazz,string $pointcut,string $advice,array $aopBehaviors = []):void
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
     * 匹配是否满足条件的切面
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $clazz 目标类
     * @param string $method 目标方法
     * @return Aspect|boolean false 表示匹配不到
     */
    public function matchAspect(string $clazz,string $method)
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
     * 执行目标方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $target 目标对象
     * @param string $method 对象方法
     * @param array $parameters 方法参数
     * @return mixed;
     */
    public function execute($target,string $method, array $parameters = [])
    {
        $expression =  $this->buildPointcut(get_class($target),$method);
        $aspect = $this->matchAspect($expression,$method);
        if ($aspect === false) {
            // 未找到拦截点,直接执行方法
            return call_user_func_array([$target,$method],$parameters);
        }

        return $aspect->doAdvice($target,$method,$parameters);
    }

    protected function buildPointcut(string $clazz,string $method):string
    {
       return str_replace('\\','.',$clazz);
    }

}
