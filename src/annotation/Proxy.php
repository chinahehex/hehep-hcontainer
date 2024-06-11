<?php
namespace hehe\core\hcontainer\annotation;

use  hehe\core\hcontainer\ann\base\Annotation;

/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Proxy
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
    public function __construct($attrs = [])
    {

        foreach ($attrs as $attr=>$value) {
            if ($attr == "value") {
                $this->proxyHandler = $value;
            } else {
                $this->$attr = $value;
            }
        }
    }
}
