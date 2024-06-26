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
     * 系统默认属性
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected static $sysAttr = ['_attrs','_scope','_ref','_lazy','_func','class','_single','_init','_args','_onProxy','_proxyHandler'];

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
    private $reflection = null;

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

    protected $_formatArgs = null;

    protected $_formatAttrs = null;

    /**
     * @var ContainerManager
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
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $id bean id
     * @param string $ref bean 别名
     * @param $clazz $clazz　对应class
     */
    public function __construct($id ,$ref = null,$clazz = null)
    {
        // 属性赋值
        if (is_array($id)) {
            if (!empty($id)) {
                $id = $this->formatAttrs($id);
                foreach ($id as $attr=>$value) {
                    if ($value != null) {
                        $this->$attr = $value;
                    }
                }
            }
        } else {
            $this->_id = $id;
            $this->_ref = $ref;
            $this->class = $clazz;
        }

        if ($this->_scope === null) {
            $this->_scope = self::DEFAULT_SCOPE;
        }
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
                return $this->getContainerManager()->getBean($this->_ref);
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
        $parameters = $this->buildArgs($args);
        $object = $classReflection->make($parameters);
        // 设置其他属性
        if ($this->_attrs != null) {
            $this->buildAttrs();
            foreach ($this->_formatAttrs as $name=>$value) {
                $object->$name = $value;
            }
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
     * 构建类的构造参数(args)
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $args 构造参数
     * @return array
     */
    protected function buildArgs(array $args):array
    {
        if (is_null($this->_formatArgs)) {
            $this->_formatArgs = $this->buildParams($this->_args);
        }

        $parameters = $this->_formatArgs;
        // 合并参数
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
    protected function buildAttrs():void
    {
        if (is_null($this->_formatAttrs)) {
            $this->_formatAttrs = $this->buildParams($this->_attrs);
        }
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
        if ($this->reflection == null) {
            $this->reflection = new ClassReflection($this->class,$this);
        }

        return $this->reflection;
    }

    /**
     * 格式化参数
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $params 参数
     * @return array
     */
    protected function buildParams(array $params = []):array
    {
        if (empty($params)) {
            return [];
        }

        foreach ($params as $name=>$value) {
            if (is_array($value) && $this->isAssoc($value)) {
                $value = $this->buildParams($value);
            } else {
                if ($value instanceof Definition) {
                    $value = $value->make();
                } else {
                    if (is_string($value)) {
                        if (preg_match(static::BEAN_REF_REGEX, $value, $match) ) {
                            $ref_type = $match[1];
                            if ($ref_type === 'lazy') {
                                $definition = $this->newDefinition(['_ref'=>$match[2],'_lazy'=>true]);
                            } else {
                                $definition = $this->newDefinition(['_ref'=>$match[2]]);
                            }

                            $value = $definition->make();
                        } else if (preg_match(static::PARAMS_REGEX, $value, $match)) {
                            $func_name = $match[1];
                            $func_params = $match[2];
                            $func_params_arr = explode(static::PARAMS_SPLIT_CHARACTER,$func_params);
                            $definition = $this->newDefinition(['_func'=>[$func_name,$func_params_arr]]);
                            $value = $definition->make();
                        }
                    } else if ($value instanceof Ref) {
                        $definition = $this->newDefinition(['_ref'=>$value->ref,'_lazy'=>$value->lazy]);
                        $value = $definition->make();
                    }
                }
            }

            $params[$name] = $value;
        }

        return $params;
    }

    /**
     * 整理类自定义参数
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param  array $attrs 参数
     * @return array
     */
    protected function formatAttrs(array $attrs = []):array
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
