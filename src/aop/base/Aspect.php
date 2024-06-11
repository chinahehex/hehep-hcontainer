<?php
namespace hehe\core\hcontainer\aop\base;

use Throwable;

/**
 * aop 管理器
 *<B>说明：</B>
 *<pre>
 * 切面类
 *</pre>
 */
class Aspect
{

    const ADVICE_BEFORE = 'before';
    const ADVICE_AROUND = 'around';
    const ADVICE_AFTER = 'after';
    const ADVICE_AFTERTHROWING = 'afterThrowing';
    const ADVICE_AFTERRETURNING = 'afterReturning';

    /**
     * 通知点行为
     *<B>说明：</B>
     *<pre>
     * 基本格式:['方法拦截点'=>['位置'=>'行为列表']]
     *</pre>
     * @var AopBehavior[]
     */
    protected $advices = [];


    protected $cache_method_advices = [];


    /**
     * 匹配规则
     * @param string $method
     */
    public function matchAspect($method)
    {
        $match_result = false;

        $behavior_list = [];
        foreach ($this->advices as $pointcut=>$advice) {
            if (($pointcut == $method) || (preg_match('/^' . $pointcut . '$/', $method) === 1)) {
                $match_result = true;
                foreach ($advice as $pos=>$behaviors) {
                    if (isset($behavior_list[$pos])) {
                        $behavior_list[$pos] = $behavior_list[$pos] + $behaviors;
                    } else {
                        $behavior_list[$pos] = $behaviors;
                    }
                }
            }
        }

        if ($match_result) {
            $this->cache_method_advices[$method] = $behavior_list;
        } else {
            $this->cache_method_advices[$method] = false;
        }

        return $match_result;
    }


    /**
     * 通知点行为
     *<B>说明：</B>
     *<pre>
     * 基本格式:['通知点位置'=>'行为列表',]
     *</pre>
     * @param string $advice
     * @param AopBehavior[] AopBehavior
     */
    public function addBehavior($advice,$behaviors = [],$pointcut = '')
    {
        if (is_string($behaviors)) {
            $behaviors = explode(',',$behaviors);
        }

        // 类
        if (isset($this->advices[$pointcut][$advice])) {
            $this->advices[$pointcut][$advice] = $this->advices[$pointcut][$advice] + $behaviors;
        } else {
            $this->advices[$pointcut][$advice] = $behaviors;
        }
    }

    public function hasAdvice($method,$advice)
    {
        if (isset($this->cache_method_advices[$method][$advice])) {
            return true;
        } else {
            return false;
        }
    }

    public function getAdviceBehaviors($method,$advice)
    {
        if (isset($this->cache_method_advices[$method][$advice])) {
            return $this->cache_method_advices[$method][$advice];
        } else {
            return null;
        }
    }

    /**
     * 执行通知行为
     *<B>说明：</B>
     *<pre>
     * 基本格式:['通知点位置'=>'行为列表',]
     *</pre>
     * @param object $target
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws Throwable
     */
    public function doAdvice($target,$method, $parameters)
    {
        $pointcutContext = new PointcutContext();
        $pointcutContext->target = $target;
        $pointcutContext->method = $method;
        $pointcutContext->parameters = $parameters;

        $returnResult = null;
        try {
            // 环绕通知
            if ($this->hasAdvice($pointcutContext->method,self::ADVICE_AROUND)) {
                $pointcutContext->advice = self::ADVICE_AROUND;
                $this->doBehaviors($pointcutContext);
                $returnResult = call_user_func_array([$target,$method],$parameters);
                $pointcutContext->methodResult = $returnResult;
                $this->doBehaviors($pointcutContext);
            } else {
                // 前置通知
                if ($this->hasAdvice($pointcutContext->method,self::ADVICE_BEFORE)) {
                    $pointcutContext->advice = self::ADVICE_BEFORE;
                    $this->doBehaviors($pointcutContext);
                }

                $returnResult = call_user_func_array([$target,$method],$parameters);

                // 后置通知
                if ($this->hasAdvice($pointcutContext->method,self::ADVICE_AFTER)) {
                    $pointcutContext->advice = self::ADVICE_AFTER;
                    $pointcutContext->methodResult = $returnResult;
                    $this->doBehaviors($pointcutContext);
                }
            }
        } catch (Throwable $t) {
            if ($this->hasAdvice($pointcutContext->method,self::ADVICE_AFTERTHROWING)) {
                $pointcutContext->advice = self::ADVICE_AFTERTHROWING;
                $pointcutContext->methodResult = $returnResult;
                $pointcutContext->exception = $t;
                $this->doBehaviors($pointcutContext);
            }

            throw $t;
        } finally {
            if ($this->hasAdvice($pointcutContext->method,self::ADVICE_AFTERRETURNING)) {
                $pointcutContext->advice = self::ADVICE_AFTERRETURNING;
                $pointcutContext->methodResult = $returnResult;
                $this->doBehaviors($pointcutContext);
            }
        }

        return $returnResult;
    }


    /**
     * 执行行为
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param PointcutContext $pointcutCtx
     */
    protected function doBehaviors(PointcutContext $pointcutCtx)
    {

        $behaviors = $this->getAdviceBehaviors($pointcutCtx->method,$pointcutCtx->advice);
        foreach ($behaviors as $behaviorClass) {
            /** @var AopBehavior $behavior*/
            $handler = $this->buildBehaviorHandler($behaviorClass);
            call_user_func_array($handler,[$pointcutCtx]);
        }
    }

    protected function buildBehaviorHandler($handler)
    {

        $handler = '\\' . str_replace(".","\\",$handler);

        $newClassStatus = false;
        if (strpos($handler,"@@") !== false) {
            list($handlerClass,$handlerMethod) = explode("@@",$handler);
        } else if (strpos($handler,"@") !== false) {
            list($handlerClass,$handlerMethod) = explode("@",$handler);
            $newClassStatus = true;
        } else {
            $handlerClass = $handler;
            $newClassStatus = true;
        }

        if (empty($handlerMethod)) {
            $handlerMethod = 'handle';
        }

        if ($newClassStatus) {
            return [new $handlerClass(),$handlerMethod];
        } else {
            return [$handlerClass,$handlerMethod];
        }
    }

}
