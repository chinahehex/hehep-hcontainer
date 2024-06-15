<?php
namespace hehe\core\hcontainer\proxy;
use ReflectionClass;

/**
 * 代理构建工具类
 *<B>说明：</B>
 *<pre>
 * 代理对象不能直接使用其属性,需通过set,get 方法
 *</pre>
 */
class BeanProxy
{
    /**
     * 生成代理类
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $className 类路径
     * @return string
     */
    public static function buildProxyClass($className)
    {
        $proxyClassName = static::buildProxyClassName($className);
        // 判断类是否存在
        if (!class_exists($proxyClassName)) {
            $proxyClassTemplate = static::buildProxyClassTemplate($className,$proxyClassName,ProxyFullClassTemplate::class);
            eval($proxyClassTemplate);
        }

        return $proxyClassName;
    }

    /**
     * 生成代理类模板
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $className 代理类模板类名
     * @param string $proxyClassName 代理类类名
     * @param string $proxyClassTemplateClass 代理模板类名
     * @return string
     */
    static public function buildProxyClassTemplate($className,$proxyClassName = null,$proxyClassTemplateClass = null)
    {
        if ($proxyClassTemplateClass === null) {
            $proxyClassTemplateClass = ProxyEasyClassTemplate::class;
        }

        if ($proxyClassName === null) {
            $proxyClassName = static::buildProxyClassName($className);
        }

        return $proxyClassTemplateClass::build($className,$proxyClassName);
    }

    /**
     * 生成代理类名称
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $className 类名称
     * @return string
     */
    static public function buildProxyClassName($className)
    {
        $proxyClassName = str_replace("\\", '_', $className);

        return $proxyClassName . '_proxy';
    }
}
