<?php
namespace hehe\core\hcontainer\annotation;

use  hehe\core\hcontainer\ann\base\Annotation;

/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Ref
{
    public $ref;

    public $lazy = false;

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
                $this->ref = $value;
            } else {
                $this->$attr = $value;
            }
        }
    }
}
