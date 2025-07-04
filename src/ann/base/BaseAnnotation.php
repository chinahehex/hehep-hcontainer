<?php
namespace hehe\core\hcontainer\ann\base;

/**
 * Annotation 元注解类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class BaseAnnotation
{
    /**
     * 获取格式化后参数
     * @param array $args 构造参数
     * @param string $valueName 第一个构造参数对应的属性名
     * @return array
     * @throws \ReflectionException
     */
    protected function getArgParams(array $args = [],string $valueName = ''):array
    {
        // php 注解
        $values = [];
        if (!empty($args)) {
            if (is_string($args[0]) || is_null($args[0])) {
                $arg_params = (new \ReflectionClass(get_class($this)))->getConstructor()->getParameters();
                foreach ($arg_params as $index => $param) {
                    $name = $param->getName();
                    $value = null;
                    if (isset($args[$index])) {
                        $value = $args[$index];
                    } else {
                        if ($param->isDefaultValueAvailable()) {
                            $value = $param->getDefaultValue();
                        }
                    }

                    if (!is_null($value)) {
                        $values[$name] = $value;
                    }
                }
            } else if (is_array($args[0])) {
                $values = $args[0];
            }
        }

        $value_dict = [];
        foreach ($values as $name => $value) {
            if (is_null($value)) {
                continue;
            }

            if ($name == 'value' && $valueName != '') {
                $value_dict[$valueName] = $value;
            } else {
                $value_dict[$name] = $value;
            }
        }


        return $value_dict;
    }

    /**
     * 获取格式化后参数
     * @param array $args 构造参数
     * @param string $valueName 第一个构造参数对应的属性名
     */
    protected function injectArgParams(array $args = [],string $valueName = ''):void
    {
        $values = $this->getArgParams($args,$valueName);

        foreach ($values as $name=>$value) {
            $this->{$name} = $value;
        }
    }
}
