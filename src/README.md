# hehep-hcontainer

## 介绍
- hehep-hcontainer 是一个 di 容器,提供类的实例化,对象属性依赖注入,AOP 拦截,容器文件扫描,等等功能

## 安装
- 直接下载:
- **gitee下载**:
```
git clone git@gitee.com:chinahehex/hehep-hcontainer.git
```

- **github下载**:
```
git clone git@github.com:chinahehex/hehep-hcontainer.git
```
- 命令安装：
```
composer require hehepx/hehep-hcontainer
```


## Bean组件

### 常规定义Bean
```php
$beanDefinition = [
    'id'=>'user',// bean 别名
    'class'=>'\site\service\User\User', # 类路径
    '_single'=> true, // 是否单例,默认是单例,
    '_scope'=> 'app', // 对象作用域,request 请求作用域
    '_init'=> 'init', // 初始化方法, 对象创建后调用设置的方法(设置属性完成后调用),
    '_onProxy'=>false,// bean 是否启用代理类,一般用于切面aop
    '_proxyHandler'=>"",// 代理事件,每调用一次对象方法,自动触发代理事件
    '_args'=> [], // true 构造方法参数，支持索引，关联数组
    '_attrs'=> [  // 类其他属性，直接注入
        'attr1'=>'26',
        'attr2'=>'26',
    ],
    'attr3'=>'类其他属性3',
    'attr4'=>'类其他属性4',
    'attr5Bean'=>'<func::bean>',// 属性调用函数, 自动调用func函数(bean) 获取属性值,
    'attr6Bean'=>'<ref::address>',// 属性对应另一个bean(user),| 之后为bean 的参数
];
```

### 注解定义bean
- 注解说明
```
通过@bean注解标签对bean进行描述,注解属性与bean定义属性一致
基本格式如下
@bean("user") 定义bean id的属性
@bean("user",_scope="app") 定义bean 作用域属性
@bean("user",_scope=true,_onProxy=true)

```
- 示例代码
```php
use hehe\core\hcontainer\annotation\Bean;

/**
 * Class UserBean
 * @package hcontainer\tests\common
 * @Bean('user')
 */
class UserBean
{
    /**
     * 姓名
     * @var
     */
    public $real_name;

    public function ok()
    {
        return true;
    }
}
```

### 注册bean
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();

// 注册bean
$hcontainer->register('user',UserinfoBean::class);

// 注册bean,增加其他配置
$hcontainer->register('user',UserinfoBean::class,['_single'=>true]);

// 批量注册
 $beans = [
    'userinfo'=>['class'=>UserinfoBean::class]
];
$hcontainer->batchRegister($beans);

```

### 实例化bean
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();
$beans = [
    'user'=>['class'=>'app\server\User'],
];

$hcontainer->batchRegister($beans);

// 获取bean对象
$user = $hcontainer->getBean('user');

// 创建新对象
$new_user = $hcontainer->make('user');

// 创建新对象,并提供构造参数
$userinfo = $hcontainer->make('user',["hehe"]);

```



### Bean作用域
```php

```

## 依赖注入

### 构造函数注入
```php
/**
 * 用户地址
 */
class UserAddress
{
    /**
     * 地址名称
     * @var string
     */
    public $name;

    /**
     * 详细地址
     * @var string
     */
    public $address;

    public function __construct($name,$address)
    {
        $this->name = $name;
        $this->address = $address;
    }
}

// bean 定义
$beans = [
    'userAddress'=>[
       'class'=>'\user\UserAddress',
       '_args'=>['公司地址','西沙群岛']
    ],
];

```
### 属性赋值注入
```php
/**
 * 用户地址
 */
class UserAddress
{
    /**
     * 地址名称
     * @var string
     */
    public $name;

    /**
     * 详细地址
     * @var string
     */
    public $address;

    public function __construct($name,$address)
    {
        $this->name = $name;
        $this->address = $address;
    }
}
// bean 定义
$beans = [
    'userAddress'=>[
       'class'=>'\user\UserAddress',
       'name'=>'公司地址',
       'address'=>'西沙群岛',
     ],
];

// 或者
$beans = [
    'userAddress'=>[
       'class'=>'\user\UserAddress',
       '_attrs'=>[
          'name'=>'公司地址',
          'address'=>'西沙群岛'
       ]
     ],
];

```

### 属性注入Bean对象
```php
namespace user\service;
/**
 * 用户类
 */
class User
{
    /**
     * 用户名
     * @var string
     */
    public $name;
   
    /**
     * 用户角色
     * @var Role
     */
    public $role;
}

// 角色类
class Role
{

    public function ok()
    {
        return 'ok';
    }
}

// bean 定义
$beans = [
    'user'=>[
       'class'=>'user\service\user',
       'name'=>'公司地址',
       'role'=>'<ref::role>'
     ],
     
    'role'=>[
       'class'=>'user\service\Role',
    ]
];

```

### 属性注解Bean对象
```php
namespace user\service;
use hehe\core\hcontainer\annotation\Ref;
/**
 * 用户类
 */
class User
{
    /**
     * 用户名
     * @var string
     */
    public $name;
   
    /**
     * 用户角色
     * @Ref("role")
     * @var Role
     */
    public $role;
}

// 角色类
class Role
{

    public function ok()
    {
        return 'ok';
    }
}

// bean 定义
$beans = [
    'user'=>[
       'class'=>'user\service\user',
       'name'=>'公司地址',
     ],
     
    'role'=>[
       'class'=>'user\service\Role',
    ]
];

```



## 容器扫描及注解
- 容器扫描说明
```
启用扫描后,程序自动查找指定命名空间的所有文件,收集注解信息,并将收集的注解信息交给对应的处理器处理
```

### 扫描规则
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();

// 扫描指定类命名空间下所有类
$hcontainer->addScanRule(ContainerManager::class,App::class);

// 扫描指定路径下的所有类文件,格式['扫描的命名空间','命名空间对应的路径']
$hcontainer->addScanRule(['hehe\core\hcontainer','d:/work/web/app'],['hehe\core\hcontainer1','d:/work/web/app1']);

// 添加优先扫描规则
$hcontainer->addFirstScanRule(ContainerManager::class,App::class);

// 开始扫描
$hcontainer->startScan();

```

### 注解处理器
- 说明
```
注解处理器作用:专门用于处理扫描收集到的注解信息
注册处理器:正常情况下,只要处理器继承了AnnotationProcessor类,都会被扫描注册至系统
优先处理器:如果想优先执行处理器,则可以添加优先处理器
自定义处理器:如想重写处理器的规则,则可以添加自定义处理器
```

- 定义注解处理器
```php
use hehe\core\hcontainer\ann\base\AnnotationProcessor;
class BeanProcessor extends AnnotationProcessor
{
    // 实现以下方法即可
    // 注解类方式
    public function annotationHandlerClazz($annotation,$clazz){}
    
    // 注解类方法方式
    public function annotationHandlerMethod($annotation,$clazz,$method){}
    
    // 注解类属性方式
    public function annotationHandlerAttribute($annotation,$clazz,$attribute){}
    
    // 扫描结束处理方法
    public function endScanHandle()
    {
        // 注册bean信息到容器
    }
}

```

- 注册处理器
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();

// 注册单个处理器
$hcontainer->addProcessor("hehe\core\hcontainer\annotation\BeanProcessor");

// 注册优先处理器
$hcontainer->addFirstProcessor("hehe\core\hcontainer\annotation\BeanProcessor");

// 注册自定义处理器[旧注解器类,新注解处理器类]
$hcontainer->addCustomProcessors(["hehe\core\hcontainer\annotation\BeanProcessor","hehe\core\hcontainer\annotation\NewBeanProcessor"]);

```

- 注解与处理器绑定
```php
// 必须指定Bean注解对应的处理器"hehe\core\hcontainer\annotation\BeanProcessor"

use  hehe\core\hcontainer\ann\base\Annotation;
/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Bean
{
    public $id;
    public $scope;
    public $single;

    public function __construct($attrs = [])
    {
        foreach ($attrs as $attr=>$value) {
            if ($attr == "value") {
                $this->id = $value;
            } else {
                $this->$attr = $value;
            }
        }
    }
}
```

## AOP 切面拦截
- 说明
```
方法切面,拦截方法调用,在调用方法之前,之后设置拦截点，并在拦截点插入行为业务,比如日志,获取缓存数据等

```

- 默认拦截点

拦截点标识 | 说明 
----------|-------------
`before`  | 业务行为会在调用目标方法之前执行
`after`  | 业务行为会在调用目标方法之后执行,如目标方法发生异常,则不会执行
`around`  | 业务行为会在调用目标方法之前与之后执行(即一前一后执行两次),如目标方法发生异常,则不会执行之后的方法
`afterThrowing`  | 调用目标方法时发生异常,则会执行此拦截点的业务行为
`afterReturning`  | 调用目标方法后，无论是否发生异常,都会执行此拦截点的业务行为，相当于异常的finally

- 调用方法之前,记录日志
```php

// 定义日志行为类
namespace admin\service;
use hehe\core\hcontainer\aop\base\AopBehavior;
use hehe\core\hcontainer\proxy\ProxyHandler;

class LogHser extends AopBehavior
{
    public function invoke($target, $method, $parameters, $returnResult, $t = null)
    {
        // TODO: Implement invoke() method.
        echo "log';
    }
}

// 定义业务类
namespace admin\service;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\aop\annotation\Before;

/**
 * @Bean(id="address")
 */
class Address
{
    public $name;

    /**
     * 调用方法之前,先执行LogHser行为
     * @Before("admin\service\LogHser")
     * @return string
     */
    public function add($addr)
    {
        // 插入地址入数据库
    }
}

// 获取bean Address 对象
use hehe\core\hcontainer\ContainerManager
$containerManager = new ContainerManager();
$address = $containerManager->getBean('address')
// 执行add 方法之前,先执行LogHser 的invoke 方法
$address->add([]);

```

- AOP 提供以下切入点 
    - before(Aspect.ADVICE_BEFORE):调用目标方法之前调用aop 行为事件
    - around(Aspect.ADVICE_AROUND):调用目标方法之前,之后,调用aop 行为事件
    - after(Aspect.ADVICE_AFTER):调用目标方法之后,调用aop 行为事件
    - afterThrowing(Aspect.ADVICE_AFTERTHROWING):调用目标方法时抛出异常,调用aop 行为事件
    - afterReturning(Aspect.ADVICE_AFTERRETURNING):调用目标方法返回值时,调用aop 行为事件


## 注解
```
注解主要用于收集数据,比如标识bean,url 地址定义
```

- 定义注解处理器
```php
class BeanProcessor extends AnnotationProcessor
{
    // 实现以下方法即可
    // 注解类方式
    public function annotationHandlerClazz($annotation,$clazz)
    {
    
    }
    
    // 注解类方法方式
    public function annotationHandlerMethod($annotation,$clazz,$method)
    {
    
    }
    
    // 注解类属性方式
    public function annotationHandlerAttribute($annotation,$clazz,$attribute)
    {
    
    }
}
```
- 定义注解
```php
namespace hehe\core\hcontainer\annotation;

use  hehe\core\hcontainer\annotation\Annotation;

/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Ref
{
    public $ref;

    public function __construct($attrs = [])
    {
        foreach ($attrs as $attr=>$value) {
            if ($attr == "value") {
                $this->ref = $value;
            } else {
                $this->$attr = $value;
            }
        }
    }
}

```
- 注解使用示例
```php
namespace admin\service;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\annotation\Proxy;
use hehe\core\hcontainer\aop\annotation\Before;
use hehe\core\hcontainer\aop\annotation\Around;
use hehe\core\hcontainer\annotation\Ref;

/**
 * @Bean(id="address")
 */
class Address
{
    public $name;

    /**
     * account 值为account bean 对象
     * @var string
     * @Ref("account");
     */
    public $account;

    public function __construct()
    {
        
    }
}

```
- 默认注解
    - Bean 标识某类为bean 对象
    - Ref 标识类属性值为另一个bean
```php
// Bean 注解,以下为部分代码

/**
 * 指定Bean注解对应的处理器
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Bean
{
    // bean 唯一id
    public $id;
    // 对象属性
    public $attrs;
    // bean 作用域
    public $scope;
    // 值为另一个bean 对象
    public $ref;
    // 对应的类名,默认为标识的类名
    public $class;
    // 是否单例
    public $single;
    // 定义实例化后初始化方法
    public $init;
    // 定义构造方法参数
    public $args;
    // 是否启用代理类
    public $onProxy;
    // 定义代理类的事件
    public $proxyHandler;
}

// Ref 注解,以下为部分代码
/**
 * 指定Ref注解对应的处理器
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Ref
{
    public $ref;

    public function __construct($attrs = [])
    {
        foreach ($attrs as $attr=>$value) {
            if ($attr == "value") {
                $this->ref = $value;
            } else {
                $this->$attr = $value;
            }
        }
    }
}
```
    


bean 
bean 定义
注册bean
实例化bean
作用域()

扫描(scan)
代理(proxy)
切面(aop)
注解(annotation)








