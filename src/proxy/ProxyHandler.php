<?php
namespace hehe\core\hcontainer\proxy;

use \hehe\core\hcontainer\ContainerManager;

/**
 * 代理事件处理类
 *<B>说明：</B>
 *<pre>
 *  一个代理类,对应一个代理事件
 *</pre>
 */
class ProxyHandler
{

    /**
     * @var \hehe\core\hcontainer\ContainerManager
     */
    protected $containerManager;

    public function getContainerManager():ContainerManager
    {
        return $this->containerManager;
    }

    public function setContainerManager(ContainerManager $containerManager):void
    {
        $this->containerManager = $containerManager;
    }

    /**
     * 被代理对象
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var object
     */
    protected $target;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param  object
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * 调用被代理类方法
     *<B>说明：</B>
     *<pre>
     * 统一入口,可以实现拦截器aop 等等功能
     *</pre>
     * @param string $method 方法名
     * @param array $parameters 参数
     * @return mixed
     */
    public function invoke(string $method, array $parameters)
    {
        return call_user_func_array([$this->target, $method], $parameters);
    }

    /**
     * 设置属性
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $name 属性名
     * @param mixed $value 属性值
     * @return mixed
     */
    public function setAttr($name, $value)
    {
        $this->target->$name = $value;
    }

    /**
     * 获取属性值
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $name 属性名
     * @return mixed
     */
    public function getAttr($name)
    {
        return $this->target->$name;
    }
}
