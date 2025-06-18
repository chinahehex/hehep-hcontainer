<?php
namespace hehe\core\hcontainer\proxy;

use hehe\core\hcontainer\base\Definition;

/**
 * 代理事件处理类
 *<B>说明：</B>
 *<pre>
 *  一个代理类,对应一个代理事件
 *</pre>
 */
class ProxyHandler
{

    public $args = [];

    /**
     * @var Definition
     */
    public $definition;

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
    public function __construct()
    {

    }

    public function getInstance()
    {
        if ($this->target === null) {
            if (!empty($this->definition->getRef())) {
                $this->target = $this->definition->getContainerManager()->getBeanId($this->definition->getRef());
            } else {
                $this->target = $this->definition->createObject($this->args);
            }

            $this->args = [];
        }

        return $this->target;
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
        $aopManager = $this->definition->getContainerManager()->getAopManager();

        return $aopManager->execute($this->getInstance(),$method,$parameters);
    }

}
