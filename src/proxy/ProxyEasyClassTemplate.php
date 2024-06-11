<?php
namespace hehe\core\hcontainer\proxy;

/**
 * 简易代理类生成工具
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class ProxyEasyClassTemplate
{
    /**
     * 生成代理类入口
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $className 目标类名
     * @param string $proxyClassName 代理类名称
     * @return string
     */
    static public function build($className,$proxyClassName)
    {
        // 定义代理类名,以及对应的代理事件属性名称
        $id = uniqid('', false);
        $handlerPropertyName = '__handler' . $id;

        $proxyClassTemplate = "class $proxyClassName{
            private \$$handlerPropertyName;
            public function __construct(\$handler)
            {
                \$this->{$handlerPropertyName} = \$handler;
            }\r\n";

        // Methods
        $proxyClassTemplate .= self::buildMethodTemplate($handlerPropertyName);
        $proxyClassTemplate .= "\r\n}";

        return $proxyClassTemplate;
    }

    /**
     * 生成代理类方法模板
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $proxyHandlerPropertyName 代理事件处理属性名称
     * @return string
     */
    static private function buildMethodTemplate($proxyHandlerPropertyName)
    {
        // set,get方法
        $methods = [];
        $setMethodsTemplate = "";
        $setMethodsTemplate .= " public function __set(\$name, \$value)\r\n{";
        $setMethodsTemplate .= "\r\nreturn \$this->{$proxyHandlerPropertyName}->setAttr(\$name, \$value);";
        $setMethodsTemplate .= "\r\n}";
        $methods[] = $setMethodsTemplate;
        $getMethodsTemplate = "";
        $getMethodsTemplate .= " public function __get(\$name)\r\n{";
        $getMethodsTemplate .= "\r\nreturn \$this->{$proxyHandlerPropertyName}->getAttr(\$name);";
        $getMethodsTemplate .= "\r\n}";
        $methods[] = $getMethodsTemplate;

        // call 魔术方法
        $callMethodsTemplate = "public function __call(\$method, \$parameters){
        return \$this->{$proxyHandlerPropertyName}->invoke(\$method, \$parameters);
        }";

        $methods[] = $callMethodsTemplate;

        return implode("\r\n",$methods);
    }
}