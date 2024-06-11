<?php
namespace hehe\core\hcontainer\base;

use ReflectionClass;

/**
 * bean 类反射对象
 *<B>说明：</B>
 *<pre>
 * 通过反射获取类的构造参数信息,提供创建对象操作
 *</pre>
 */
class ClassReflection
{
    const PARAMS_REGEX =  '/<(.+)::([^>]+)?>/';
    const BEAN_REF_REGEX =  '/<ref::([^>]+)?>/';
    const PARAMS_SPLIT_CHARACTER = '|';

    /**
     * 目标类
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $clazz;

    /**
     * php 原始反射类
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var ReflectionClass
     */
    protected $reflection = null;

    /**
     * 目标类的构造参数
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $parameters = [];

    /**
     * bean 定义对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Definition
     */
    protected $definition = null;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function __construct($clazz,$definition)
    {
        $this->clazz = $clazz;
        $this->definition = $definition;
    }

    /**
     * 创建对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $args 构造参数
     * @return object
     */
    public function make($args = [])
    {
        // 获取默认参数
        $params = $this->buildParmas($args);
        $reflection = $this->getReflection();

        if (empty($params)) {
            return $reflection->newInstance();
        } else {
            return $reflection->newInstanceArgs($params);
        }
    }

    /**
     * 合并构造参数
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $args 外部构造参数
     * @param array $params 默认构造参数
     * @return object
     */
    protected function mergeParams($args,$params)
    {
        // 合并参数
        if (!empty($args)) {
            if ($this->isAssoc($args)) {
                $params = array_merge($params,$args);
                $params = array_values($params);
            } else {
                $params = array_values($params);
                foreach ($args as $index => $param) {
                    $params[$index] = $param;
                }
            }
        }

        return $params;
    }

    /**
     * 获取构造方法参数
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function getParams()
    {
        if (count($this->parameters) > 0) {
            return $this->parameters;
        }

        $reflection = $this->getReflection();
        $constructor = $reflection->getConstructor();
        $parameters = [];
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();
                if ($param->isDefaultValueAvailable()) {// 如果有默认值，就说明不可能是类对象
                    $defaultValue = $param->getDefaultValue();
                    if (is_string($defaultValue)) {
                        if (preg_match(static::BEAN_REF_REGEX, $defaultValue, $match) ) {
                            $funcName = $match[1];
                            $definition = new Definition(['_ref'=>$funcName]);
                            $definition->setContainerManager($this->definition->getContainerManager());
                            $defaultValue = $definition->make([]);
                        } else if (preg_match(static::PARAMS_REGEX, $defaultValue, $match)) {
                            $funcName = $match[1];
                            $funcParams = $match[2];
                            $funcParams = explode(static::PARAMS_SPLIT_CHARACTER,$funcParams);
                            $definition = new Definition(['_func'=>[$funcName,$funcParams]]);
                            $definition->setContainerManager($this->definition->getContainerManager());
                            $defaultValue = $definition->make([]);
                        }
                    }

                    $parameters[$name] = $defaultValue;
                } else {
//                    $paramClass = $param->getClass();
//                    // 对象变量
//                    if ($paramClass !== null) {
//                        $paramValue = Instance::make($paramClass->getName());
//                    }
                    $paramValue = null;
                    $parameters[$name] = $paramValue;
                }
            }
        }

        $this->parameters = $parameters;

        return $this->parameters;
    }

    /**
     * 构建真实默认构造参数
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function buildParmas($args)
    {
        $params = $this->getParams();
        $params = $this->mergeParams($args,$params);

        if (!empty($params)) {
            foreach ($params as $index => $value) {
                if ($value instanceof Definition) {
                    $value = $value->make([]);
                }

                $params[$index] = $value;
            }
        }

        return $params;
    }

    /**
     * 获取反射对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function getReflection()
    {
        if ($this->reflection ==  null) {
            $this->reflection = new ReflectionClass($this->clazz);
        }

        return $this->reflection;
    }

    /**
     * 判断数组是否关联数组
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param  array $array 目标数组
     * @return bool true 表示关联数组 false 表示索引数组
     */
    private function isAssoc($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

}