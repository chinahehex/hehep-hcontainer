<?php
namespace hehe\core\hcontainer\annotation;

use hehe\core\hcontainer\ann\base\Ann;
use hehe\core\hcontainer\ann\base\Annotation;
use Attribute;
/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
#[Annotation("hehe\core\hcontainer\annotation\BeanProcessor")]
#[Attribute]
class Ref extends Ann
{
    public $ref;

    public $lazy = false;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array|string $value
     */
    public function __construct($value = null,bool $lazy = null,string $ref = null)
    {
        $this->injectArgParams(func_get_args(),'ref');
    }
}
