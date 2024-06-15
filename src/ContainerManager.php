<?php
namespace hehe\core\hcontainer;

use hehe\core\hcontainer\ann\AnnotationManager;
use hehe\core\hcontainer\aop\AopManager;
use hehe\core\hcontainer\base\Container;
use hehe\core\hcontainer\base\Definition;


/**
 * 容器管理器类
 * 实现了依赖注入，控制反转，服务注册功能，实现了完全松耦合
 * 依赖基本格式
 * [
 *      'class'=>'app\models\UserFinder',// 类路径
 *      '_single'=>true, // 是否单例 默认是单例,
 *      '_scope'=>'app',// 对象作用域,app 应用级别，forever 永远不失效
 *      '_init'=>'init',// 初始化方法,对象创建后调用设置的方法(设置属性完成后调用)
        '_args'=>[], // 构造方法参数，支持索引，关联数组
        '_attrs'=>[ // 类其他属性，直接注入
            'name55'=>'26',
            'name56'=>'26',
            'name57'=>new Definition([
 *              '_ref'=>'account',// name57 为另一个bean 为account 的对象
            ]),
        ]
 * ]
 *
 *
 * 常用格式
 * [
 * 'class'=>'app\models\User',// 类路径
 *      'attr1'=>'26',
 *      'attr2'=>'',
 *      'attrBean'=>'<func::user>',属性调用函数,自动调用func函数,函数参数为account
 *      'account'=>'<ref::account1>'
 *      'name57'=>new Definition([
 *      '_ref'=>'account',// name57 为另一个bean 为account 的对象
 *    ]),
 * ]
 *
 * 属性调用函数
 * 'class'=>'app\models\UserFinder',// 类路径
 *      'account'=>'<hbean::account>',属性值为其他bean,自动调用hbean函数,函数参数为account
 *      'account'=>'<hbean::account|1>',多个参数
 * ]
 *
 * 定义的属性name55,name56,name57 自动归入_attr 属性(类的属性)
 */
class ContainerManager
{
    const CLASS_KEY_NAME = "class";
    const SCOPE_REQUEST = 'request';

    /**
     * aop 切面对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var \hehe\core\hcontainer\aop\AopManager
     */
    protected $aopManager;

    /**
     * 是否开启注解扫描
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var bool
     */
    protected $scan = false;

    /**
     * 注解管理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var \hehe\core\hcontainer\ann\AnnotationManager
     */
    protected $annManager;

    /**
     * 扫描规则定义
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $scanRules = [];

    /**
     * 容器作用域
     *<B>说明：</B>
     *<pre>
     *  app 应用级别,应用启动,开始生效
     *  request 请求级别,每次请求时生效,请求结束后失效
     *</pre>
     * @var Container[]
     */
    public $scopeContainer = [
        'app'=>null,
        'request'=>null
    ];

    /**
     * bean定义对象列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Definition[]
     */
    protected $definitions = [];

    /**
     * bean 组件配置
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $components = [];

    /**
     * bend类名与bean id 的对应关系
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $clazzBeanIdMap = [];

    /**
     * bean 对象列表
     *<B>说明：</B>
     *<pre>
     *  存储单例
     *</pre>
     * @var array
     */
    protected $beans = [];

    /**
     * 容器作用域处理
     *<B>说明：</B>
     *<pre>
     *  存储单例
     *</pre>
     * @var array
     */
    protected $scopeHandlers = null;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function __construct($attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $attr=>$value) {
                $this->$attr = $value;
            }
        }
    }

    public function getAopManager()
    {
        if ($this->aopManager == null) {
            $this->aopManager = new AopManager();
        }

        return $this->aopManager;
    }

    public function getAnnManager():AnnotationManager
    {
        if ($this->annManager == null) {
            $this->annManager = new AnnotationManager([
                'scanRules'=>$this->scanRules,
                'containerManager'=>$this,
            ]);
        }

        return $this->annManager;
    }

    /**
     * 添加扫描规则
     * @param mixed ...$scanRules
     * @return $this
     */
    public function addScanRule(...$scanRules):self
    {
        $this->getAnnManager()->addScanRule(...$scanRules);

        return $this;
    }

    /**
     * 添加优先扫描规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array<命名空间,命名空间文件路径> $scanRules 扫描路径
     * @return static
     */
    public function addFirstScanRule(...$scanRules):self
    {
        $this->getAnnManager()->addFirstScanRule(...$scanRules);

        return $this;
    }

    /**
     * 自定义处理器集合
     *<B>说明：</B>
     *<pre>
     *  由于替换旧的注解处理器
     *</pre>
     * @var array<旧注解器类,新注解处理器类>
     */
    public function addCustomProcessors(...$customProcessors):self
    {
        $this->getAnnManager()->addCustomProcessors(...$customProcessors);

        return $this;
    }

    /**
     * 添加优先处理器
     * @param mixed ...$processors
     */
    public function addFirstProcessor(...$processors):self
    {
        $this->getAnnManager()->addFirstProcessor(...$processors);

        return $this;
    }

    /**
     * 开始扫描
     */
    public function startScan():self
    {
        $this->getAnnManager()->start();

        return $this;
    }

    public function setScopeHandlers($scopeHandlers):void
    {
        $this->scopeHandlers = $scopeHandlers;
    }

    public function setScopeHandler(string $scope,$handler):void
    {
        $this->scopeHandlers[$scope] = $handler;
    }

    /**
     * 获取范围容器对象
     *<B>说明：</B>
     *<pre>
     *  存储单例
     *</pre>
     * @param string $scope 作用域
     * @return Container
     */
    public function getScopeContainer(string $scope = ''):Container
    {

        if (!isset($this->scopeHandlers[$scope])) {
            if (!isset($this->scopeContainer[$scope])) {
                $container = $this->makeContainer($scope);
                $this->scopeContainer[$scope] = $container;
            } else {
                $container = $this->scopeContainer[$scope];
            }
        } else {
            $scopeContainerFunc = $this->scopeHandlers[$scope];
            $container = call_user_func($scopeContainerFunc);
        }

        return $container ;
    }

    /**
     * 创建一个新容器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Container $scope 容器作用域
     * @return Container
     */
    public function makeContainer(string $scope = ''):Container
    {
        return new Container($scope);
    }

    /**
     * 获取bean 实例
     *<B>说明：</B>
     *<pre>
     *  获取定义bean 的实例
     *</pre>
     * @param string $beanId
     * @param array $args
     * @return object
     */
    public function getBean(string $beanId,array $args = [])
    {
        $definition = $this->getDefinition($beanId);

        // 获取对应的容器
        $container = $definition->getContainer();
        if (!$container->hasBean($beanId)) {
            $bean = $definition->make($args);
            // 如果是单例,则将bean对象注入容器中
            if ($definition->isSingle()) {
                $container->setBean($beanId,$bean);
            }
        } else {
            $bean = $container->getBean($beanId);
        }

        return $bean;
    }

    /**
     * 根据类路径获取bean对象,
     * @param string $clazz
     * @param array $args
     * @return object
     */
    public function getBeanByClass(string $clazz,array $args = [])
    {
        $beanId = $this->getBeanId($clazz);
        if (!is_null($beanId)) {
            return $this->getBean($beanId,$args);
        } else {
            return null;
        }
    }

    /**
     * 创建一个新对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $beanId
     * @param array $args
     * @return object
     */
    public function make(string $beanId,array $args = [])
    {
        // 创建bean 定义对象
        $definition = $this->getDefinition($beanId);

        //  创建对象
        return $definition->make($args);
    }

    /**
     * 获取bean 定义对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $beanId 组件id
     * @return Definition
     */
    public function getDefinition(string $beanId):Definition
    {
        if (isset($this->definitions[$beanId])) {
            return $this->definitions[$beanId];
        }

        // bean定义不存在
        if (isset($this->components[$beanId])) {
            $component = $this->components[$beanId];
        } else {
            $component = [
                self::CLASS_KEY_NAME=>$beanId
            ];
        }

        $definition = new Definition($component);
        $definition->setContainerManager($this);
        $this->definitions[$beanId] = $definition;

        return $this->definitions[$beanId];
    }

    /**
     * 批量注册组件
     * <B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $components 组件配置
     */
    public function batchRegister(array $components = []):void
    {
        foreach ($components as $id=>$component) {
            $this->appendComponent($id,$component);
        }
    }

    /**
     * 注册单个组件
     * <B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $id 组件标识
     * @param string $class 组件类
     * @param array $component 组件配置
     */
    public function register(string $id,string $class = null,array $component = []):void
    {
        $component = array_merge($component,[
            'id'=>$id,
            'class'=>$class
        ]);

        $this->appendComponent($id,$component);

        return ;
    }

    /**
     * 返回指定类路径对应的bean id
     * @param string $clazz
     * @return string|null
     */
    public function getBeanId(string $clazz):?string
    {
        if (isset($this->clazzBeanIdMap[$clazz])) {
            return $this->clazzBeanIdMap[$clazz];
        } else {
            return null;
        }
    }

    /**
     * 追加bean配置
     * <B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $beanId bean id 或bean class path
     * @param array $component 配置
     */
    public function appendComponent(string $beanId,array $component):void
    {
        $component = $this->formatComponent($beanId,$component);

        $bid = $component['id'];
        if (isset($this->components[$bid])) {
            $this->components[$bid] = $component + $this->components[$bid];
        } else {
            $this->components[$bid] = $component;
        }

        $this->clazzBeanIdMap[$component['class']] = $bid;
    }

    public function hasComponent(string $beanId):bool
    {
        if (isset($this->components[$beanId])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断类是否配置过bean
     * @param string $clazz
     * @return bool
     */
    public function hasBeanByClass(string $clazz):bool
    {
        $beanId = $this->getBeanId($clazz);
        if (is_null($beanId)) {
            return false;
        } else {
            return true;
        }
    }

    public function getComponents():array
    {
        return $this->components;
    }

    protected function formatComponent(string $id,array $component):array
    {
        if (!isset($component['id'])) {
            $component['id'] = $id;
        }

        if (is_null($component['class'])) {
            $component['class'] = $id;
        } else {
            $class = $component['class'];
            if ($component['class'] instanceof \Closure) {
                $component['class'] = $id;
                $component['_func'] = $class;
            } else {
                $component['class'] = $class;
            }
        }

        $bean_class = $component['class'];
        if (isset($this->clazzBeanIdMap[$bean_class])) {
            $component['id'] = $this->clazzBeanIdMap[$bean_class];
        }

        return $component;
    }
}
