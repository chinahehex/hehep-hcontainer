# hehep-hcontainer

## 介绍
- hehep-hcontainer 是一个 di容器,提供类的实例化工具组件
- 支持注释注解
- 支持PHP原生注解
- 支持类属性,构造参数依赖注入
- 支持AOP切面
- 支持Bean作用域

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
composer require hehex/hehep-hcontainer
```
- 依赖包
```
如果需要注释注解,则需要安装doctrine
composer require "doctrine/annotations"

如是PHP8,可以使用PHP原生注解,或两者同时使用
```
## Bean组件

### 常规定义Bean
```php
$beanDefinition = [
    'id'=>'user',// bean 别名
    'class'=>'\site\service\User\User', # 类路径
    '_single'=> true, // 是否单例,默认是单例,
    '_scope'=> 'request', // 对象作用域,request 请求作用域
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
通过@Bean注解器对bean进行描述,注解器属性与"常规定义Bean"一致
基本格式如下
@Bean("user") 定义bean id的属性
@Bean("user",_scope="app") 定义bean 作用域属性
@Bean("user",_scope=true,_onProxy=true)

```
- 示例代码
```php
namespace app\services\UserBean;
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
    public $name;

    public function __construct($name = '')
    {
        $this->name = $name;
    }
}
```

### 注册Bean
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();

// 注册bean
$hcontainer->register('user',UserinfoBean::class);

// 注册bean,增加其他配置
$hcontainer->register('user',UserinfoBean::class,['_single'=>true]);

// 批量注册
 $beans = [
    // 格式['bean别名'=>["class"=>"bean 类路径","其他参数1"=>'xxx']]
    'userinfo'=>['class'=>UserinfoBean::class]
];
$hcontainer->batchRegister($beans);

```

### 实例化Bean
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();
$beans = [
    'user'=>['class'=>'app\services\UserBean'],
];

$hcontainer->batchRegister($beans);

// 获取bean对象
$user = $hcontainer->getBean('user');

// 创建新bean对象
$new_user = $hcontainer->make('user');

// 创建新bean对象,并提供构造参数,索引数组
$userinfo = $hcontainer->make('user',["hehe"]);

// 创建新bean对象,并提供构造参数,关联数组
$userinfo = $hcontainer->make('user',["name"=>"hehe"]);

```

### Bean作用域
- 说明
```
bean 默认只有一种作用域,即永久作用域,如需实现其他作用域容器,则可通过
设置作用域容器事件来实现,比如需实现"request" 请求级别的作用域,则需要设置request对应的容器获取事件
```

- 完整示例代码
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();
// 定义request 级别类(每次请求完成,此对象都会被回收)
class App
{
    public $container;
    
    public function __construct(ContainerManager $hcontainer)
	{
	    // 从容器管理器创建一个空容器
        $this->container = $hcontainer->makeContainer();
    }
}

$app = new App($hcontainer);

// 设置作用域容器事件
$hcontainer->setScopeHandler('request',function()use($app){
    return $app->container;
});

// 或者
$hcontainer->setScopeHandlers(['request'=>function()use($app){
    return $app->container;
}]);

// 业务代码
$beans = [
    'user'=>['class'=>'app\servers\User','_scope'=>'request'],
];

// user bean 从$app->container 容器中取出
$user = $hcontainer->getBean('user');

// 注销$app变量,其$container属性也会注销,随着request容器的回收,Bean $user 对象也会被注销
unset($app);

```

## 依赖注入

### 构造函数注入
- 说明
```
构造函数参数有三种方式注入Bean
方式1:定义参数类型为Bean类
方式2:参数变量默认值为<ref:xxxx>,xxx 为指定的bean标识
方式3:参数变量默认值为<lazy:xxxx>,xxx 为指定的bean标识
```

- 示例代码
```php

/**
 * 角色类
 */
class RoleBean
{
    
}

/**
 * 用户类
 */
class UserBean
{
    /**
     * 地址名称
     * @var string
     */
    public $name;

    /**
     * 角色对象
     * @var RoleBean
     */
    public $argRole;
    
     /**
     * 角色对象
     * @var RoleBean
     */
    public $argRefRole;
    
    /**
     * 角色对象
     * @var RoleBean
     */
    public $argLazyRole;
    
    // 如RoleBean 配置过bean,则系统会自动从容器中获取RoleBean对象传入
    public function __construct($name,RoleBean $argRole,$argRefRole = '<ref:role>',$argLazyRole = '<lazy:role>')
    {
        $this->name = $name;
        $this->argRole = $argRole;
        $this->argRefRole = $argRefRole;
        $this->argLazyRole = $argLazyRole;
    }
}

// bean 定义
$beans = [
    'user'=>[
       'class'=>'\user\UserBean',
       '_args'=>['公司地址']
    ],
    'role'=>['class'=>'\user\RoleBean']
];

```
### 属性赋值注入
```php
/**
 * 角色类
 */
class RoleBean
{
    
}

/**
 * 用户类
 */
class UserBean
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
}
// 对应的bean 定义
$beans = [
    'user'=>[
       'class'=>'\user\UserBean',
       'name'=>'公司地址',
     ],
];

// 或者
$beans = [
    'user'=>[
       'class'=>'\user\UserBean',
       '_attrs'=>[
          'name'=>'公司地址',
       ]
     ],
];

```

### 属性注入Bean
- 说明
```
以<ref::bean别名> 的方式注入Bean 对象
以Ref注解的方式注入Bean 对象
```

- 示例代码
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
     * @var Role
     */
    public $role1;
    
     /**
     * 用户角色
     * @Ref("role")
     * @var Role
     */
    public $role2;
}

/**
 * 角色类
 */
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
       'role1'=>'<ref::role>'
     ],
     
    'role'=>[
       'class'=>'user\service\Role',
    ]
];

```



### Bean代理
- 说明
```
代理概念:在用户端与目标类之间插入一个中间类,用户端通过中间类操作目标类(中间类继承目标类,中间类删除所有属性,重写目标类所有方法)
代理流程:
    开启代理后,先创建代理类对象存储在容器中,当在调用代理方法或使用属性时都触发目标类对象的创建，目标对象存储在“代理事件”对象中,
    基本流程:客户端->代理类->代理事件->创建目标对象->目标方法
    AOP基本流程:客户端->代理类->代理事件->创建目标对象->AOP切面->目标方法
开启代理:Bean定义配置_onProxy=true,AOP相关注解(After,Before等等),延迟注入(lazy),即会自动进入代理模式
解决问题:代理模式实现AOP切面功能,间接实现了"延迟注入",解决了相互依赖导致的死循环问题
```

- Bean定义代理示例代码
```php
// Bean 定义
$benans =  [
    ['user'=>['class'=>'user\service\user','_onProxy'=>true]]
];

```

- Bean注解代理示例代码
```php
use hehe\core\hcontainer\annotation\Bean;
/**
 * @bean("user",_onProxy=true)
 */
class UserBean
{

}

```

- AOP注解代理示例代码
```php
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\aop\annotation\After;

/**
 * @bean("user")
 * @After("hcontainer\tests\common\LogBehavior@log",pointcut=".+Action")
 */
class UserBean
{
    
    /**
     * @After("hcontainer\tests\common\LogBehavior")
     */
    public function doAfter(){}
}
```

- 延迟注入代理示例代码
```php
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\annotation\Ref;

/**
 * @bean("user")
 */
class UserBean
{
    
    /**
     * $annRole 为代理对象
     * @Ref("role","lazy"=>true)
     * @var RoleBean
     */
    public $annRole;
}
```

### 延迟注入
- 说明
```
由于在注入的过程中,容易出现相互依赖而导致的死循环问题,延迟注入的方式有效的解决此问题,
延迟注入会自动开启代理模式,如果Bean已经开启过代理模式，注入的是Bean单例代理对象,如Bean 未开启代理模式,则创建新的代理对象
```

- 示例代码
```php

// bean 定义
$beans = [
    'user'=>[
       'class'=>'user\service\user',
       'name'=>'公司地址',
       'role1'=>'<ref::role>'
     ],
     
    'role'=>[
       'class'=>'user\service\Role',
       'user'=>'<lazy::user>'
    ]
];

use hehe\core\hcontainer\annotation\Ref;
// 注解方式
class RoleBean
{
    /**
     * @Ref("user",lazy=true)
     * @var UserBean
     */
    public $user;
    
}

```

## 扫描,注解处理器
- 说明
```
开启扫描后,程序会自动查找指定命名空间下的所有类文件,并收集注解信息,同时将收集到的注解信息交给对应的注解处理器来处理业务，
比如与Bean相关的注解器Bean,Ref都被指定由"hehe\core\hcontainer\annotation\BeanProcessor"来处理
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
注解处理器作用:专门用于处理收集到的注解信息
注解处理器基类:都必须继承"hehe\core\hcontainer\ann\base\AnnotationProcessor"类
优先处理器:如果想优先执行某个处理器,则可以将此处理器添加到"优先处理器"集合中
重置处理器:如想重写某个处理器的规则,则可以将此处理器添加到"重置处理器"集合中
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

- 注册(优先/重置)处理器
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();

// 注册优先处理器
$hcontainer->addFirstProcessor("hehe\core\hcontainer\annotation\BeanProcessor");

// 注册重置处理器[旧注解器类,新注解处理器类]
$hcontainer->addCustomProcessors(["hehe\core\hcontainer\annotation\BeanProcessor","hehe\core\hcontainer\annotation\NewBeanProcessor"]);

```

- 注解与处理器绑定
```php
//可通过"hehe\core\hcontainer\annotation\Annotation"注解器将"注解器"与"注解处理器"绑定
//Annotation格式:@Annotation("注解处理器类路径")
use  hehe\core\hcontainer\ann\base\Annotation;
/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Bean
{
    public $id;
    public $_scope;
    public $_single;

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

## AOP方法拦截
- 说明
```
切面(aspect):AOP切面通俗点讲就是拦截类方法调用,在调用目标方法之前,之后设置拦截点，并在拦截点插入行为业务,比如日志,获取缓存数据等
实现原理:在执行创建目标对象时动态的生成代理类，通过代理类操作目标类
通知点(advice):目标方法之前,之后,异常时切入业务行为的位置点
拦截点表达式(pointcut):目标方法或匹配方法名的正则表达
```

### 默认通知点位置

通知点位置 | 说明 
----------|-------------
`before`  | 业务行为会在调用目标方法之前执行
`after`  | 业务行为会在调用目标方法之后执行,如目标方法发生异常,则不会执行
`around`  | 业务行为会在调用目标方法之前与之后执行(即一前一后执行两次),如目标方法发生异常,则不会执行之后的方法
`afterThrowing`  | 调用目标方法时发生异常,则会执行此通知点的业务行为
`afterReturning`  | 调用目标方法后，无论是否发生异常,都会执行此通知点的业务行为，相当于异常的finally

### 定义"业务行为"
- 说明
```
每个"业务行为"方法都会传入"拦截点上下文"（PointcutContext）对象,其属性如下
advice:拦截点位置,如after,before
target:目标对象
method:目标方法
parameters:目标方法传入的参数
methodResult:执行目标方法后返回的结果
exception:执行目标方法时抛出的异常对象

```
- 示例代码
```php
namespace app\behaviors;
use hehe\core\hcontainer\aop\base\AopBehavior;
use hehe\core\hcontainer\aop\base\PointcutContext;
class LogBehavior extends AopBehavior
{
    // 默认调用方法
    public function handle(PointcutContext $pointcutCtx)
    {
        
        $pointcutCtx->advice;// 通知点位置
        $pointcutCtx->target;// 调用的对象
        $pointcutCtx->method;// 被调用的方法
        $pointcutCtx->parameters;// 被调用方法的参数
        $pointcutCtx->methodResult;// 执行被调用方法后返回的结果
        $pointcutCtx->exception;// 执行被调用方法后发生异常后,抛出的异常对象
    }
    
    // 方法1
    public function handle1(PointcutContext $pointcutCtx)
    {
        // 业务行为代码
    }
    
    // 静态方法1
    public static function handle2(PointcutContext $pointcutCtx)
    {
        // 业务行为代码
    }
}

```
### 定义目标类
```php
namespace  app\beans;

use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\aop\annotation\Advice;
use hehe\core\hcontainer\aop\annotation\After;
use hehe\core\hcontainer\aop\annotation\Before;
use hehe\core\hcontainer\aop\annotation\Around;
use hehe\core\hcontainer\aop\annotation\AfterThrowing;

/**
 * @bean("user")
 * 
 * 匹配以"Action"结尾的方法名,并在调用目标方法之后切入"hcontainer\tests\common\LogBehavior@log"业务行为
 * @After("hcontainer\tests\common\LogBehavior@log",pointcut=".+Action")
 */
class UserBean
{
     /**
     * 在执行方法之后执行“LogBehavior” 类的handle方法
     * @After("hcontainer\tests\common\LogBehavior")
     */
    public function doAfter($user,$msg = '')
    {
        return $msg;
    }

    /**
     * 在执行方法之前执行“LogBehavior”类的handle方法
     * @Before("hcontainer\tests\common\LogBehavior")
     */
    public function doBefore($user,$msg = '')
    {
        return $msg;
    }

    /**
     * 在执行方法之前与之后执行“LogBehavior”类的handle方法
     * @Around("hcontainer\tests\common\LogBehavior")
     */
    public function doAround($user,$msg = '')
    {
        return $msg;
    }

    /**
     * 在执行方法时发生异常，则执行"LogBehavior"
     * @AfterThrowing("hcontainer\tests\common\LogBehavior")
     */
    public function doAfterThrowing($user,$msg = '')
    {
        throw new Exception($msg);
    }

    /**
     * 在执行方法之后以对象的方式调用“LogBehavior”的"log"方法
     * @After("hcontainer\tests\common\LogBehavior@handle1")
     */
    public function doNewMethod($user,$msg = '')
    {
        return $msg;
    }

    /**
     * 在执行方法之后以类的方式调用“LogBehavior”的静态"log"方法
     * @After("hcontainer\tests\common\LogBehavior@@handle2")
     */
    public function doNew2Method($user,$msg = '')
    {
        return $msg;
    }


    // 以下两方法被类切面拦截
    public function do1Action($user,$msg = '')
    {
        return $msg;
    }

    public function do2Action($user,$msg = '')
    {
        return $msg;
    }
}

```

## 注解
```
注解主要用于收集用户在代码中自定义的数据,并交由注解处理器处理业务
```

### 定义注解处理器
```php
class BeanProcessor extends AnnotationProcessor
{
    // 实现以下方法即可
    // 注解类方式
    public function annotationHandlerClazz($annotation,$clazz){}
    
    // 注解类方法方式
    public function annotationHandlerMethod($annotation,$clazz,$method){}
    
    // 注解类属性方式
    public function annotationHandlerAttribute($annotation,$clazz,$attribute){}
}
```
### 定义注解器
- 说明
```
定义注解器时，必须为其指定注解处理器
可通过"hehe\core\hcontainer\annotation\Annotation"注解器将"注解器"与"注解处理器"绑定
Annotation格式:@Annotation("注解处理器类路径")
```

- 代码示例
```php
namespace hehe\core\hcontainer\annotation;
use  hehe\core\hcontainer\annotation\Annotation;

/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Ref
{
    public $ref;

    public function __construct($value = null,bool $lazy = null,string $ref = null)
    {
       // 无需处理构造参数,调用injectArgParams将构造参数直接赋值给注解器属性
       // 如需处理构造参数,通过$this->getArgParams(func_get_args(),'ref') 获取格式化后的构造参数
        $this->injectArgParams(func_get_args(),'ref');
    }
}

```
- 注解器使用示例
```php
namespace admin\service;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\annotation\Ref;

/**
 * @Bean(id="user")
 */
class User
{
    public $name;

    /**
     * role 值为Role bean对象
     * @Ref("Role");
     */
    public $role;
}

```

### PHP原生注解
- 说明
```
原生注解与注释注解的用户基本一致,格式如下
#[注解器("第一个构造参数值")]
#[注解器("第一个构造参数值",属性名称1:属性值1,属性名称2:属性值2)]
#[注解器(属性名称1:属性值1,属性名称2:属性值2)]
#[注解器(array(属性名称1=>属性值1,属性名称2=>属性值2))]
```

- 定义注解器1
```php
namespace hehe\core\hcontainer\annotation;

use  hehe\core\hcontainer\annotation\Annotation;
use Attribute;

#[Annotation("hehe\core\hcontainer\annotation\BeanProcessor")]
#[Attribute]
class Ref
{
    public $ref;

    public function __construct($value = null,bool $lazy = null,string $ref = null)
    {
        // 自己处理构造参数
    }
}

```

- 定义注解器2

```php
namespace hehe\core\hcontainer\annotation;

use hehe\core\hcontainer\ann\base\Ann;
use hehe\core\hcontainer\annotation\Annotation;
use Attribute;

#[Annotation("hehe\core\hcontainer\annotation\BeanProcessor")]
#[Attribute]
class Ref extends Ann
{
    public $ref;

    public function __construct($value = null,bool $lazy = null,string $ref = null)
    {
        // 无需处理构造参数,调用injectArgParams将构造参数直接赋值给注解器属性
        $this->injectArgParams(func_get_args(),'ref');
    }
}

```

- 定义注解器3

```php
namespace hehe\core\hcontainer\annotation;

use hehe\core\hcontainer\ann\base\Ann;
use hehe\core\hcontainer\annotation\Annotation;
use Attribute;

#[Annotation("hehe\core\hcontainer\annotation\BeanProcessor")]
#[Attribute]
class Advice extends Ann
{
    // 通知点位置
    public $advice;

    // 业务行为集合
    public $behaviors = [];

    // 拦截点表达式
    public $pointcut = '';

    public function __construct($value = null,string $pointcut = null,string $advice = null,string $behaviors = null)
    {
        // 需处理构造参数,获取格式化的构造参数
        $values = $this->getArgParams(func_get_args(),'behaviors');
        foreach ($values as $name=>$val) {
            if ($name == 'behaviors') {
                if (is_string($val)) {
                    $this->behaviors = explode(',',$val);
                } else {
                    $this->behaviors = $val;
                }
            } else {
                $this->$name = $val;
            }
        }
    }
}

```

- 注解器示例
```php
namespace admin\service;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\annotation\Ref;


#[Bean("user")]
class User
{
    public $name;

    /**
     * role 值为Role bean对象
     * 
     */
     #[Ref("role",lazy:true)]
    public $role;
    
    #[After("hcontainer\\tests\common\LogBehavior@@log2")]
    public function okaop1($log,$msg)
    {

        return $msg;
    }
    
    #[After(behaviors:"hcontainer\\tests\common\LogBehavior@@log2")]
    public function okaop2($log,$msg)
    {

        return $msg;
    }
}

```

### 默认注解器列表

注解器 |说明|格式
---------|----------|------
hehe\core\hcontainer\annotation\Bean|标识此类为bean对象|@Bean("user"),@Bean(id="user"),@Bean("user",_onProxy=>true)
hehe\core\hcontainer\annotation\Proxy|标识此类启用了代理,同时会生成代理对象,一般用于切面|@Proxy()
hehe\core\hcontainer\annotation\Ref|标识类属性为bean对象|@Ref("user"),@Ref("user",lazy=true)
hehe\core\hcontainer\aop\annotation\After|aop切面注解器,用于在执行目标方法之后切入“业务行为”|@After("业务行为类路径"),@After("业务行为类路径@方法"),@After("业务行为类路径@@静态方法")
hehe\core\hcontainer\aop\annotation\Before|aop切面注解器,用于在执行目标方法之前切入“业务行为”|与@After格式一致
hehe\core\hcontainer\aop\annotation\Around|aop切面注解器,用于在执行目标方法之前与之后切入“业务行为”|与@After格式一致
hehe\core\hcontainer\aop\annotation\AfterThrowing|aop切面注解器,用于在执行目标方法时发生异常时切入“业务行为”|与@After格式一致
hehe\core\hcontainer\aop\annotation\AfterReturning|aop切面注解器,用于在执行目标方法之后,无论是否发生异常,都会切入“业务行为”,类似异常的finally|与@After格式一致










