<?php
namespace hehe\core\hcontainer\aop\annotation;

use hehe\core\hcontainer\aop\base\Aspect;
use hehe\core\hcontainer\ann\base\Annotation;
use Attribute;
/**
 * 在目标方法之前切入业务行为
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 * @Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")
 */
#[Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")]
#[Attribute]
class Before extends Advice
{
    public $advice = Aspect::ADVICE_BEFORE;
}
