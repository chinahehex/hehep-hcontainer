<?php
namespace hehe\core\hcontainer\annotation;

use hehe\core\hcontainer\ann\base\BaseAnnotation;
use hehe\core\hcontainer\ann\base\Annotation;
use Attribute;

/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
#[Annotation("hehe\core\hcontainer\annotation\BeanProcessor")]
#[Attribute]
class Proxy extends BaseAnnotation
{

    public $_onProxy = true;

    public $_proxyHandler;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $attrs
     */
    public function __construct($value = null,?string $_proxyHandler = null)
    {
        $this->injectArgParams(func_get_args(),'_proxyHandler');
    }
}
