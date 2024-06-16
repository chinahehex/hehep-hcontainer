<?php
namespace hehe\core\hcontainer\aop\annotation;

use hehe\core\hcontainer\aop\base\Aspect;
use hehe\core\hcontainer\ann\base\Annotation;
use Attribute;
/**
 * 在方目标法前后切入业务行为
 *<B>说明：</B>
 *<pre>
 * 如果在执行目标方法的过程中发生异常,则不会执行之后(After)的业务行为
 *</pre>
 * @Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")
 */
#[Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")]
#[Attribute]
class Around extends Advice
{
    public $advice = Aspect::ADVICE_AROUND;
}
