<?php
namespace hehe\core\hcontainer\annotation;

use  hehe\core\hcontainer\ann\base\Annotation;

/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Bean
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
     * @param array $attrs
     */
    public function __construct($attrs = [])
    {
        foreach ($attrs as $attr=>$value) {
            if ($attr == "value") {
                $this->id = $value;
            } else {
                $this->$attr = $value;
            }
        }
    }
}
