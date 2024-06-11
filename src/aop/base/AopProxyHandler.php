<?php
namespace hehe\core\hcontainer\aop\base;
use hehe\core\hcontainer\aop\AopManager;
use hehe\core\hcontainer\proxy\ProxyHandler;

/**
 * aop 代理事件
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class AopProxyHandler extends ProxyHandler
{
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
    public function invoke($method, $parameters)
    {
        $containerManager = $this->getContainerManager();
        /** @var AopManager $aopManager */
        $aopManager = $containerManager->getAopManager();

        return $aopManager->execute($this->target,$method,$parameters);
    }

}