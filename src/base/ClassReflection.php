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
    const BEAN_REF_REGEX =  '/^<(ref|lazy)::([^>]+)?>/';
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
    protected $parameters;

    /**
     * bean 定义对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Definition
     */
    protected $definition;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function __construct(string $clazz,Definition $definition)
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
    public function make(array $args = [])
    {
        // 获取默认参数
        $arg_params = $this->buildConstructParmas($args);
        $reflection = $this->getReflection();

        if (empty($arg_params)) {
            return $reflection->newInstance();
        } else {
            return $reflection->newInstanceArgs($arg_params);
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
    protected function mergeConstructParams(array $args,array $params)
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
     * @return array ['参数名1'=>'默认值','参数名2'=>'默认值']
     */
    protected function getConstructParams():array
    {
        if ($this->parameters !== null) {
            return $this->parameters;
        }

        $reflection = $this->getReflection();
        $constructor = $reflection->getConstructor();
        $parameters = [];
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();
                $defaultValue = null;
                $type = $param->getType();
                if (!is_null($type) && !$param->getType()->isBuiltin()) {
                    // 非系统类型
                    if ($type instanceof \ReflectionNamedType) {
                        $class = $type->getName(); // 获取类名
                    } else {
                        $class = $param->getClass()->getName();
                    }
                    // 如果类是bean,则自动从容器读取bean 对象
                    $hcontainer = $this->definition->getContainerManager();
                    if ($hcontainer->hasBeanByClass($class)) {
                        $defaultValue = $this->definition->newDefinition(['_ref'=>$hcontainer->getBeanId($class)]);
                    } else {
                        // interface_exists,判断是否接口
                    }
                } else {
                    $defaultValue = $param->getDefaultValue();
                    if (is_string($defaultValue)) {
                        if (preg_match(static::BEAN_REF_REGEX, $defaultValue, $match) ) {
                            $ref_type = $match[1];
                            if ($ref_type === 'lazy') {
                                $defaultValue = $this->definition->newDefinition(['_ref'=>$match[2],'_lazy'=>true]);
                            } else {
                                $defaultValue = $this->definition->newDefinition(['_ref'=>$match[2]]);
                            }
                        } else if (preg_match(static::PARAMS_REGEX, $defaultValue, $match)) {
                            $func_name = $match[1];
                            $func_params = $match[2];
                            $func_params_arr = explode(static::PARAMS_SPLIT_CHARACTER,$func_params);
                            $defaultValue = $this->definition->newDefinition(['_func'=>[$func_name,$func_params_arr]]);
                        }
                    }
                }

                $parameters[$name] = $defaultValue;
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
    protected function buildConstructParmas(array $args = []):array
    {
        $constructParams = $this->getConstructParams();
        if (!empty($constructParams)) {
            $beanArgs = $this->mergeConstructParams($args,$constructParams);
            if (!empty($beanArgs)) {
                foreach ($beanArgs as $index => $value) {
                    if ($value instanceof Definition) {
                        $beanArgs[$index] = $value->make();
                    } else {
                        $beanArgs[$index] = $value;
                    }
                }
            }

            return $beanArgs;
        } else {
            return $constructParams;
        }
    }

    /**
     * 获取反射对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function getReflection():ReflectionClass
    {
        if ($this->reflection ===  null) {
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
    private function isAssoc($array):bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

}
