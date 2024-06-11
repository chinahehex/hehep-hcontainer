<?php
namespace hehe\core\hcontainer\aop\annotation;

use hehe\core\hcontainer\aop\base\Aspect;
use  hehe\core\hcontainer\ann\base\Annotation;

/**
 * 在方法后切入通知点
 *<B>说明：</B>
 *<pre>
 * 抛出异常则不会切入
 *</pre>
 * @Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")
 */
class After extends Advice
{
    public $advice = Aspect::ADVICE_AFTER;
}
