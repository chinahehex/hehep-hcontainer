<?php
namespace hehe\core\hcontainer\aop\annotation;

use hehe\core\hcontainer\aop\base\Aspect;
use hehe\core\hcontainer\ann\base\Annotation;

/**
 * 在方法之前切入通知点
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 * @Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")
 */
class Before extends Advice
{
    public $advice = Aspect::ADVICE_BEFORE;
}
