<?php
namespace hehe\core\hcontainer\aop\annotation;

use hehe\core\hcontainer\aop\base\Aspect;
use  hehe\core\hcontainer\ann\base\Annotation;
use Attribute;
/**
 * 在目标方法之后切入业务行为
 *<B>说明：</B>
 *<pre>
 * 抛出异常则不会切入
 *</pre>
 * @Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")
 */
#[Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")]
#[Attribute]
class After extends Advice
{
    public $advice = Aspect::ADVICE_AFTER;
}
