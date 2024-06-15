<?php
namespace hehe\core\hcontainer\proxy;

use ReflectionMethod;
use ReflectionParameter;

/**
 * 代理类生成工具
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class ProxyFullClassTemplate
{
    /**
     * 生成代理类入口
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $className 类名称
     * @param string $proxyClassName 代理类名称
     * @return string
     */
    public static  function build($className,$proxyClassName)
    {

        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);

        // 定义代理类名,以及对应的代理事件属性名称
        $id = \uniqid('', false);
        $handlerPropertyName = 'handler' . $id;
        $proxyClassTemplate = "class $proxyClassName extends $className{
            private \${$handlerPropertyName};
            public function __construct(\$handler)
            {
                \$this->{$handlerPropertyName} = \$handler;
                
                foreach (array_keys(get_object_vars(\$this)) as \$name) {
                    if (\$name !== '{$handlerPropertyName}') {
                        unset(\$this->\$name);
                    }
                }
                
            }
            public function __get(\$name)
            {
                \$bean = \$this->{$handlerPropertyName}->getInstance();
                return \$bean->\$name;
            }
            
            public function __set(\$name,\$value)
            {
                \$bean = \$this->{$handlerPropertyName}->getInstance();
                \$bean->\$name = \$value;
            }
            
            public function __isset(\$name)
            {
                \$bean = \$this->{$handlerPropertyName}->getInstance();
                return isset(\$bean->\$name);
            }
            
            public function __unset(\$name)
            {
                \$bean = \$this->{$handlerPropertyName}->getInstance();
                unset(\$bean->\$name);
            }
            
            ";


        // Methods
        $proxyClassTemplate .= self::buildMethodTemplate($reflectionMethods, $handlerPropertyName);
        $proxyClassTemplate .= "\r\n}";

        return $proxyClassTemplate;
    }

    /**
     * 生成类方法模板
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param ReflectionMethod[] $reflectionMethods 类方法对象列表
     * @param string $proxyHandlerPropertyName
     * @return string
     */
    static private function buildMethodTemplate($reflectionMethods = [], $proxyHandlerPropertyName)
    {
        $methods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            // 构造方法,静态方法过滤
            if ($reflectionMethod->isConstructor() || $reflectionMethod->isStatic()) {
                continue;
            }

            $return_type_code = '';
            if ($reflectionMethod->hasReturnType()) {
                $refReturnType = $reflectionMethod->getReturnType();
                $return_type_code = $refReturnType->allowsNull() ? ':?' . (string)$refReturnType : ':' . (string)$refReturnType;
            }

            // 方法体
            $methodBody = "{
                return \$this->{$proxyHandlerPropertyName}->invoke('{$methodName}', func_get_args());
            }
            ";

            $methodsTemplate = "";
            $methodsTemplate .= " public function $methodName (";
            $methodsTemplate .= self::buidlMethodParamsTemplate($reflectionMethod);
            $methodsTemplate .= ')' . $return_type_code;
            $methodsTemplate .= $methodBody;

            $methods[] = $methodsTemplate;
        }

        return implode("\r\n",$methods);
    }

    /**
     * 生成方法参数模板
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param ReflectionMethod $reflectionMethod 类名称
     * @return string
     */
    static private function buidlMethodParamsTemplate(ReflectionMethod $reflectionMethod)
    {
        $reflectionParameters = $reflectionMethod->getParameters();
        $params = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            // 参数类型(\ReflectionMethod $reflectionMethod)
            $type_name = "";
            if ($reflectionParameter->hasType()) {
                $var_type = $reflectionParameter->getType();
                $type_name = $var_type->allowsNull() ? "?" . (string)$var_type : (string)$var_type;
            }

            $methodParameterName = $reflectionParameter->getName();
            if ($reflectionParameter->isPassedByReference()) {
                $paramName = " &\${$methodParameterName} ";
            } elseif ($reflectionParameter->isVariadic()) {
                $paramName = " ...\${$methodParameterName} ";
            } else {
                $paramName = " \${$methodParameterName} ";
            }

            // 参数默认值
            $parameterDefaultValue = "";
            if ($reflectionParameter->isOptional() && $reflectionParameter->isVariadic() === false) {
                $parameterDefaultValue = self::formatParameterDefaultValue($reflectionParameter);
            }

            $params[] = "{$type_name}{$paramName}{$parameterDefaultValue}";
        }

        return implode(',',$params);
    }

    /**
     * 格式化默认值
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param ReflectionParameter $reflectionParameter 参数对象
     * @return string
     */
    private static function formatParameterDefaultValue(\ReflectionParameter $reflectionParameter)
    {
        $defaultValueTpl = '';
        $defaultValue = $reflectionParameter->getDefaultValue();
        if ($reflectionParameter->isDefaultValueConstant()) {
            $defaultConst = $reflectionParameter->getDefaultValueConstantName();
            $defaultValueTpl = " = {$defaultConst}";
        } else if (\is_bool($defaultValue)) {
            $value = $defaultValue ? 'true' : 'false';
            $defaultValueTpl = " = {$value}";
        } else if (\is_string($defaultValue)) {
            $defaultValueTpl = " = '{$defaultValue}'";
        } else if (\is_int($defaultValue)) {
            $defaultValueTpl = " = {$defaultValue}";
        } else if (\is_array($defaultValue)) {
            $defaultValueTpl = ' = []';
        } else if (\is_float($defaultValue)) {
            $defaultValueTpl = " = {$defaultValue}";
        } else if (\is_object($defaultValue) || null === $defaultValue) {
            $defaultValueTpl = ' = null';
        }

        return $defaultValueTpl;
    }
}
