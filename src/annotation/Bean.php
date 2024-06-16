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
class Bean extends Ann
{
    public $id;

    public $_attrs;

    public $_scope;

    public $_ref;

    public $class;

    public $_single;

    public $_init;

    public $_args;

    public $_onProxy;

    public $_proxyHandler;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array|string $value
     */
    public function __construct(
        $value = null,
        bool $_onProxy = null,
        string $_init = null,
        string $_proxyHandler = null,
        bool $_scope = null,
        bool $_single = null,
        array $_args = null,
        array $_attrs = null,
        string $id = null
    )
    {
        $this->injectArgParams(func_get_args(),'id');
        
    }
}
