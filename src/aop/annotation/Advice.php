<?php
namespace hehe\core\hcontainer\aop\annotation;

use hehe\core\hcontainer\ann\base\Annotation;

/**
 * @Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")
 */
class Advice
{
    public $advice;

    public $behaviors = [];

    public $pointcut = '';

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $attrs
     */
    public function __construct($attrs = [])
    {
        foreach ($attrs as $attr=>$value) {
            if ($attr == "value") {
                $this->behaviors[] = $value;
            } else {
                $this->$attr = $value;
            }
        }
    }
}
