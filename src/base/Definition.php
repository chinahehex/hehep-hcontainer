<?php
namespace hehe\core\hcontainer\base;

use Exception;
use hehe\core\hcontainer\annotation\Ref;
use hehe\core\hcontainer\ContainerManager;
use hehe\core\hcontainer\proxy\BeanProxy;
use hehe\core\hcontainer\proxy\ProxyHandler;
use ReflectionClass;

/**
 * bean 定义类描述
 *<B>说明：</B>
 *<pre>
 * 主要用于定义类注入属性
 *</pre>
 */
class Definition
{

    const PARAMS_REGEX =  '/<(.+)::([^>]+)?>/';
    const BEAN_REF_REGEX =  '/^<(ref|lazy)::([^>]+)?>/';
    const PARAMS_SPLIT_CHARACTER = '|';
    const SYS_ATTR_ONPROXY = '_onProxy';
    const SYS_ATTR_PROXYHANDLER = '_proxyHandler';
    const SYS_ATTR_SINGLE = '_single';
    const SYS_ATTR_CLASS = 'class';
    const SYS_ATTR_ID = '_id';
    const SYS_ATTR_BIND = '_bind';
    const SYS_ATTR_BOOT = '_boot';
    const DEFAULT_SCOPE = 'forever';


    /**
     * bean全局唯一id
     *<B>说明：</B>
     *<pre>
     *  如未设置,则默认为对象的类名
     *</pre>
     */
    protected $_id = "";

    /**
     * 绑定其他bean或接口
     * @var array
     */
    protected $_bind = [];

    /**
     * 实在在引导应用时创建对象
     * @var bool
     */
    protected $_boot = false;

    /**
     * 系统默认属性
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected static $sysAttr = [
        '_id',
        '_boot',
        '_attrs',
        '_scope',
        '_ref',
        '_lazy',
        '_func',
        'class',
        '_single',
        '_bind',
        '_init',
        '_args',
        '_onProxy',
        '_proxyHandler'
    ];

    /**
     * 调用其他的bean对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected  $_ref = null;

    /**
     * 调用方法获取bean 对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected $_func = null;

    /**
     * 对应的类名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected  $class = null;

    /**
     * 构造方法参数，支持索引，关联数组
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected $_args = [];

    /**
     * 类其他属性，直接注入
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected $_attrs = [];

    /**
     * 作用域
     *<B>说明：</B>
     *<pre>
     *  app,request应用级别(每次请求结束后自动销毁)
     *  forever 永久级别(必须重启php 服务后才会自动销毁)
     *</pre>
     */
    protected $_scope = '';

    /**
     * 反射对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    private $_reflection = null;

    /**
     * 初始化方法
     *<B>说明：</B>
     *<pre>
     *  实例化对象后随即调用的第一个方法
     *</pre>
     */
    protected $_init = null;

    /**
     * 是否生成代理对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected $_onProxy = false;

    /**
     * 代理事件处理器
     *<B>说明：</B>
     *<pre>
     *  代理事件处理器类名
     *</pre>
     * @var string
     */
    protected $_proxyHandler;

    /**
     * 是否单例
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected $_single = true;

    /**
     * 懒代理标识
     * @var bool
     */
    protected $_lazy = false;

    /**
     * 构建后构造参数集合
     * @var null
     */
    protected $_buildArgs = null;

    /**
     * 构建后类属性集合
     * @var null
     */
    protected $_buildAttrs = null;

    /**
     * @var ContainerManager
     */
    protected $containerManager;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $config 配置
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $attributes = $this->splitAttrs($config);
            foreach ($attributes as $name=>$value) {
                if ($value != null) {
                    $this->{$name} = $value;
                }
            }
        }

        if ($this->_scope === null) {
            $this->_scope = self::DEFAULT_SCOPE;
        }
    }

    public function getContainerManager():ContainerManager
    {
        return $this->containerManager;
    }

    public function setContainerManager(ContainerManager $containerManager):void
    {
        $this->containerManager = $containerManager;
    }

    public function getId():?string
    {
        return $this->_id;
    }

    public function getRef():?string
    {
        return $this->_ref;
    }

    public function getClazz():string
    {
        return $this->class;
    }

    public function isSingle():bool
    {
        return $this->_single;
    }

    /**
     * 是否有代理
     */
    public function hasProxy():bool
    {
        return $this->_onProxy;
    }

    public function getScope():string
    {
        if ($this->_scope == null) {
            return self::DEFAULT_SCOPE;
        } else {
            return $this->_scope;
        }
    }

    /**
     * 获取组件对应的容器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return Container
     */
    public function getContainer():Container
    {
        return $this->getContainerManager()->getScopeContainer($this->getScope());
    }

    /**
     * 创建bean 对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $args
     * @return object
     * @throws \Exception;
     */
    public function make(array $args = [])
    {
        if ($this->_ref != null) {
            if ($this->_lazy) {
                $ref_definition = $this->getContainerManager()->getDefinition($this->_ref);
                if ($ref_definition->hasProxy()){
                    return $this->getContainerManager()->getBean($this->_ref);
                } else {
                    // 创建一个新的代理
                    return $ref_definition->createProxy($args);
                }
            } else {
                if (strpos($this->_ref,'\\') !== false) {
                    return $this->getContainerManager()->getBeanByClass($this->_ref);
                } else {
                    return $this->getContainerManager()->getBean($this->_ref);
                }
            }
        } else if ($this->_func != null) {
            if ($this->_func instanceof \Closure) {
                return call_user_func($this->_func);
            } else {
                return call_user_func_array($this->_func[0],$this->_func[1]);
            }
        } else {
            // 生成代理类
            if ($this->_onProxy) {
                $object = $this->createProxy($args);
            } else {
                $object = $this->createObject($args);
            }

            return $object;
        }
    }

    /**
     * 创建bean 对象
     * @param array $args
     * @return object
     */
    public function createObject(array $args = [])
    {
        // 创建对象
        $classReflection = $this->getReflection();
        $object = $classReflection->make($this->getArgs($args));
        // 设置其他属性
        if (!empty($this->_attrs)) {
            // 注入public 属性
            foreach ($this->getAttrs() as $name=>$value) {
                if ($value instanceof Definition) {
                    $object->$name = $value->make();
                } else {
                    $object->$name = $value;
                }
            }

            // 尝试set 注入属性
        }

        // 调用类对象初始化方法
        if (!is_null($this->_init)) {
            call_user_func([$object,$this->_init]);
        }

        return $object;
    }


    protected function createProxy(array $args = [])
    {
        // 创建事件类
        if ($this->_proxyHandler == null) {
            $this->_proxyHandler = ProxyHandler::class;
        }

        $proxyHandlerReflection =  new ReflectionClass($this->_proxyHandler);
        /**@var \hehe\core\hcontainer\proxy\ProxyHandler $proxyHandler*/
        $proxyHandler = $proxyHandlerReflection->newInstance($args);
        $proxyHandler->definition = $this;

        $proxyClassName = BeanProxy::buildProxyClass($this->class);
        // 创建代理对象
        $newRc = new ReflectionClass($proxyClassName);

        return $newRc->newInstance($proxyHandler);
    }

    /**
     * 获取构造参数
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $args 构造参数
     * @return array
     */
    protected function getArgs(array $args):array
    {
        if (is_null($this->_buildArgs)) {
            $this->_buildArgs = $this->buildInjectParams($this->_args);
        }

        $parameters = $this->_buildArgs;
        // 合并参数，此处有毛病
        if (!empty($args)) {
            foreach ($args as $index => $param) {
                $parameters[$index] = $param;
            }
        }

        return $parameters;
    }

    /**
     * 构建类属性(args)
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function getAttrs():array
    {
        if (is_null($this->_buildAttrs)) {
            $this->_buildAttrs = $this->buildInjectParams($this->_attrs);
        }

        return $this->_buildAttrs;
    }

    /**
     * 获取反射对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function getReflection():ClassReflection
    {
        if ($this->_reflection == null) {
            $this->_reflection = new ClassReflection($this->class,$this);
        }

        return $this->_reflection;
    }

    /**
     * 构建注入参数
     *<B>说明：</B>
     *<pre>
     *  构建构造参数,类属性
     *</pre>
     * @param array $params 参数
     * @return array
     */
    protected function buildInjectParams(array $params = []):array
    {
        if (empty($params)) {
            return [];
        }

        foreach ($params as $name=>$value) {
            if (is_array($value) && $this->isAssoc($value)) {
                $value = $this->buildInjectParams($value);
            } else {
                if (is_string($value)) {
                    if (preg_match(static::BEAN_REF_REGEX, $value, $match) ) {
                        $ref_type = $match[1];
                        if ($ref_type === 'lazy') {
                            $value = $this->newDefinition(['_ref'=>$match[2],'_lazy'=>true]);
                        } else {
                            $value = $this->newDefinition(['_ref'=>$match[2]]);
                        }

                    } else if (preg_match(static::PARAMS_REGEX, $value, $match)) {
                        $func_name = $match[1];
                        $func_params = $match[2];
                        $func_params_arr = explode(static::PARAMS_SPLIT_CHARACTER,$func_params);
                        $value = $this->newDefinition(['_func'=>[$func_name,$func_params_arr]]);
                    }
                } else if ($value instanceof Ref) {
                    $value = $this->newDefinition(['_ref'=>$value->ref,'_lazy'=>$value->lazy]);
                }
            }

            $params[$name] = $value;
        }

        return $params;
    }

    /**
     * 分离本类属性,构造参数,用户属性
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param  array $attrs 参数
     * @return array
     */
    protected function splitAttrs(array $attrs = []):array
    {
        $attributes = [];
        $customAttrs = [];
        foreach ($attrs as $name=>$value) {
            if (in_array($name,static::$sysAttr)) {
                $attributes[$name] = $value;
            } else {
                $customAttrs[$name] = $value;
            }
        }

        $argsStatus = false;
        if (isset($attributes['_args']) && is_bool($attributes['_args'])) {
            $argsStatus = $attributes['_args'];
        }

        if ($argsStatus) {
            $attributes['_args'] = [$customAttrs];
        } else {
            $attributes['_attrs'] = $customAttrs;
        }

        return $attributes;
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

    public function newDefinition(array $attrs = []):self
    {
        $definition  = new static($attrs);
        $definition->setContainerManager($this->getContainerManager());

        return $definition;
    }
}
