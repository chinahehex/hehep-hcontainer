<?php
namespace hehe\core\hcontainer\aop\annotation;

use hehe\core\hcontainer\ann\base\BaseAnnotation;
use hehe\core\hcontainer\ann\base\Annotation;
use Attribute;

/**
 * @Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")
 */
#[Annotation("hehe\core\hcontainer\aop\annotation\AdviceProcessor")]
#[Attribute]
class Advice extends BaseAnnotation
{
    // 通知点位置
    public $advice;

    // 业务行为集合
    public $behaviors = [];

    // 拦截点表达式
    public $pointcut = '';

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $value
     */
    public function __construct($value = null,string $pointcut = null,string $advice = null,string $behaviors = null)
    {
        $values = $this->getArgParams(func_get_args(),'behaviors');
        foreach ($values as $name=>$val) {
            if ($name == 'behaviors') {
                if (is_string($val)) {
                    $this->behaviors = explode(',',$val);
                } else {
                    $this->behaviors = $val;
                }
            } else {
                $this->{$name} = $val;
            }
        }
    }
}
