<?php
namespace hehe\core\hcontainer\annotation;

use hehe\core\hcontainer\ann\base\Ann;
use  hehe\core\hcontainer\ann\base\Annotation;
use Attribute;
/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
#[Annotation("hehe\core\hcontainer\annotation\BeanProcessor")]
#[Attribute]
class Proxy extends Ann
{

    public $onProxy = true;

    public $proxyHandler;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $attrs
     */
    public function __construct($value = null,string $proxyHandler = null)
    {
        $this->injectArgParams(func_get_args(),'proxyHandler');
    }
}
