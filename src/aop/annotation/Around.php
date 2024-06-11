<?php
namespace hehe\core\hcontainer\aop\annotation;

use hehe\core\hcontainer\aop\base\Aspect;
use hehe\core\hcontainer\ann\base\Annotation;

/**
 * 在方法前后切入相同的通知点
 *<B>说明：</B>
 *<pre>
 * 在方法执行前后切入，可以中断或忽略原有流程的执行
 *</pre>
 * @Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")
 */
class Around extends Advice
{
    public $advice = Aspect::ADVICE_AROUND;
}
