# hehep-hcontainer

## 介绍
> hehep-hcontainer 是一个 di容器,提供类的实例化工具组件  
> 支持注释注解  
> 支持PHP原生注解  
> 支持类属性,构造参数依赖注入  
> 支持AOP切面  
> 支持Bean作用域  

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
> 如果需要注释注解,则需要安装doctrine  
> composer require "doctrine/annotations"  
> 如是PHP8,可以使用PHP原生注解,或两者同时使用  

## Bean组件

### 常规定义Bean
```php
$beanDefinition = [
    '_id'=>'user',// bean 别名
    'class'=>'\site\service\User\User', # 类路径,
    '_boot'=>false, // 是否提前实例化对象
    '_single'=> true, // 是否单例,默认是单例,
    '_scope'=> 'request', // 对象作用域,request 请求作用域
    '_init'=> 'init', // 初始化方法, 对象创建后调用设置的方法(设置属性完成后调用),
    '_onProxy'=>false,// bean 是否启用代理类,一般用于切面aop
    '_proxyHandler'=>"",// 代理事件,每调用一次对象方法,自动触发代理事件
    '_args'=> [], // true 所有属性通过一个构造参数注入,这个参数必须是数组，支持索引，关联数组
    '_attrs'=> [  // 类其他属性，直接注入
        'attr1'=>'26',
        'attr2'=>'26',
    ],
    // 绑定其他bean或接口,即user与LoggerInterface绑定，通过LoggerInterface可获取bean对象
    '_bind'=>[
        Psr\Log\LoggerInterface::class,
    ],
    'attr3'=>'类其他属性3',
    'attr4'=>'类其他属性4',
    'attr5Bean'=>'<func::bean>',// 属性调用函数, 自动调用func函数(bean) 获取属性值,
    'attr6Bean'=>'<ref::address>',// 属性对应另一个bean(user),| 之后为bean 的参数
];
```

### 基本示例
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();
$hcontainer->startup();// 启动容器

$hcontainer->register('user',[]);// 注册user bean
$hcontainer->get('user',[]);// 获取user bean对象


```

### 注解定义bean
- 注解说明

> 通过@Bean注解器对bean进行描述,注解器属性与"常规定义Bean"一致  
> 基本格式如下  
> @Bean("user") 定义bean id的属性  
> @Bean("user",_scope="app") 定义bean 作用域属性  
> @Bean("user",_scope=true,_onProxy=true)  

- 示例代码
```php
namespace app\services\UserBean;
use hehe\core\hcontainer\annotation\Bean;

/**
 * @Bean('user')
 */
class UserBean
{
    /**
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

> bean 默认只有一种作用域,即永久作用域,如需实现其他作用域容器,则可通过  
> 设置作用域容器事件来实现,比如需实现"request" 请求级别的作用域,则需要设置request对应的容器获取事件  

- 示例代码
```php
use hehe\core\hcontainer\ContainerManager;
// 定义App应用类,每次请求会创建新App对象,当请求结束,则App被注销,App的容器container也被注销
class App
{
    public $container;
    
    public function __construct(ContainerManager $hcontainer)
	{
	    // 从容器管理器创建一个空容器
        $this->container = $hcontainer->makeContainer();
    }
}

$hcontainer = new ContainerManager();
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

### 绑定类或接口
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();

$beans = [
    'hlog'=>[
        'class'=>'hehe\core\hlogger\LogManager',
        '_bind'=>[Psr\Log\LoggerInterface::class]
     ],
];

$hcontainer->batchRegister($beans);

// 通过id 获取bean对象
$hlog = $hcontainer->getBean('hlog');

// 通过接口获取bean对象
$hlog = $hcontainer->getBeanByClass(Psr\Log\LoggerInterface::class);


```

## 依赖注入

### 构造函数注入
- 说明
> 构造函数参数有三种方式注入Bean  
> 方式1:定义参数类型为Bean类  
> 方式2:定义参数默认值为\<ref:xxxx>,xxx 为指定的bean标识  
> 方式3:定义参数默认值为\<lazy:xxxx>,xxx 为指定的bean标识

- 定义参数类型方式注入
```php
class UserBean
{
    public $role;
    
    public function __construct(RoleBean $role)
    {
        $this->role = $role;
    }
}
```

- 定义参数默认值方式注入
```php
class UserBean
{
    public $role;
    
    public function __construct($role = '<ref:role>')
    {
        $this->role = $role;
    }
}
```

- 定义参数默认值方式延迟注入
```php
class UserBean
{
    public $role;
    
    public function __construct($role = '<lazy:role>')
    {
        $this->role = $role;
    }
}
```

### 属性赋值注入
> 属性注入对应的属性必须是public属性  

```php
class UserBean
{
    public $name;
}
// 对应的bean 定义
$beans = [
    'user'=>[
       'class'=>'\user\UserBean',
       'name'=>'hehe',
     ],
];

// 或者
$beans = [
    'user'=>[
       'class'=>'\user\UserBean',
       '_attrs'=>[
          'name'=>'hehe',
       ]
     ],
];

```

### 属性注入Bean
- 说明
> 以\<ref::bean别名>默认值的方式注入Bean对象  
> 以Ref注解的方式注入Bean对象  

- 示例代码
```php
namespace user\service;
use hehe\core\hcontainer\annotation\Ref;

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

// 对应的bean定义
$beans = [
    'user'=>[
       'class'=>'user\service\user',
       'name'=>'hehe',
       'role1'=>'<ref::role>'
     ],
     
    'role'=>[
       'class'=>'user\service\Role',
    ]
];

```

### Bean代理
- 说明
> 代理概念:在用户端与目标类之间插入一个中间类,用户端通过中间类操作目标类(中间类继承目标类,中间类删除所有属性,重写目标类所有方法)  
> 代理流程:  
>  开启代理后,先创建代理类对象存储在容器中,当在调用代理方法或使用属性时都触发目标类对象的创建，目标对象存储在“代理事件”对象中,  
>  基本流程:客户端->代理类->代理事件->创建目标对象->目标方法  
>  AOP基本流程:客户端->代理类->代理事件->创建目标对象->AOP切面->目标方法  
> 开启代理:Bean定义配置_onProxy=true,AOP相关注解(After,Before等等),延迟注入(lazy),即会自动进入代理模式 
> 解决问题:代理模式实现AOP切面功能,间接实现了"延迟注入",解决了相互依赖导致的死循环问题   

- Bean定义代理示例代码
```php
// Bean定义
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

- AOP注解示例代码
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

- 延迟注入示例代码
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
     * @Ref("role","_lazy"=>true)
     * @var RoleBean
     */
    public $annRole;
}
```

### 延迟注入
- 说明
> 由于在注入的过程中,容易出现相互依赖而导致的死循环问题,延迟注入的方式有效的解决此问题,  
> 延迟注入会自动开启代理模式,如果Bean已经开启过代理模式，注入的是Bean单例代理对象,如Bean 未开启代理模式,则创建新的代理对象  

- Bean定义方式
```php
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

```

- 注解方式
```php
use hehe\core\hcontainer\annotation\Ref;
// 注解方式
class RoleBean
{
    /**
     * @Ref("user",_lazy=true)
     * @var UserBean
     */
    public $user;
    
}

```


## 扫描文件
- 说明
> 开启扫描后,程序会自动查找指定命名空间下的所有类文件,并收集注解信息,同时将收集到的注解信息交给对应的注解处理器来处理业务，  
> 比如与Bean相关的注解器Bean,Ref都被指定由"hehe\core\hcontainer\annotation\BeanProcessor"来处理  

### 扫描规则
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();

// 开启扫描开关
$hcontainer->asScan();

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
> 注解处理器作用:专门用于处理收集到的注解信息  
> 注解处理器基类:都必须继承"hehe\core\hcontainer\ann\base\AnnotationProcessor"类  
> 优先处理器:如果想优先执行某个处理器,则可以将此处理器添加到"优先处理器"集合中  
> 重置处理器:如想重写某个处理器的规则,则可以将此处理器添加到"重置处理器"集合中  

- 定义注解处理器
```php
use hehe\core\hcontainer\ann\base\AnnotationProcessor;
class BeanProcessor extends AnnotationProcessor
{
    // 自定义注解处理方法
    protected $annotationHandlers = [
        'Ref'=>'handleRefAnnotation'
    ];
    
    // 实现以下方法即可
    
    // 统一处理类,方法，类类型注解 $target_type = class,method,property
    public function handleAnnotation($annotation,string $class,string $target,string $target_type):void{}
    
    // 处理注解类
    public function handleAnnotationClass($annotation,string $class):void{}
    
    // 处理注解类方法
    public function handleAnnotationMethod($annotation,string $class,string $method):void{}
    
    // 处理注解类属性
    public function handleAnnotationProperty($annotation,string $class,string $property):void{}
    
    // 独立处理注解@Ref
    public function handleRefAnnotation($annotation,string $class,string $target,string $target_type)
    {
        
    }
    
    // 扫码结束后调用此方法
    public function handleProcessorFinish()
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
> 可通过"hehe\core\hcontainer\ann\base\Annotation"注解器将"注解器"与"注解处理器"绑定  
> Annotation格式:@Annotation("注解处理器类路径")

```php
use hehe\core\hcontainer\ann\base\Annotation;
use hehe\core\hcontainer\ann\base\BaseAnnotation;

/**
 * @Annotation("hehe\core\hcontainer\annotation\BeanProcessor")
 */
class Bean extends BaseAnnotation
{
    public $_id;
    public $_scope;
    public $_single;

    public function __construct($value = null,bool $_scope = null,bool $_single = null,string $_id = null)
    {
        $this->injectArgParams(func_get_args(),'_id');
    }
}
```

## AOP方法拦截
- 说明
> 切面(aspect):AOP切面通俗点讲就是拦截类方法调用,在调用目标方法之前或之后设置拦截点，并在拦截点插入行为业务,比如日志,获取缓存数据等  
> 实现原理:在执行创建目标对象时动态的生成代理类，通过代理类操作目标类  
> 通知点(advice):目标方法之前,之后,异常时切入业务行为的位置点  
> 拦截点表达式(pointcut):目标方法或匹配方法名的正则表达  

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
> 每个"业务行为"方法都会传入"拦截点上下文"（PointcutContext）对象,其属性如下  
> advice:拦截点位置,如after,before  
> target:目标对象  
> method:目标方法  
> parameters:目标方法传入的参数  
> methodResult:执行目标方法后返回的结果  
> exception:执行目标方法时抛出的异常对象  

- 示例代码
```php
namespace app\behaviors;
use hehe\core\hcontainer\aop\base\PointcutContext;
class LogBehavior
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
> 注解主要用于收集用户在代码中自定义的数据,并交由注解处理器处理业务  

### 定义注解处理器
> 定义注解处理器请参考[扫描-注解处理器](#注解处理器)
```php
class BeanProcessor extends AnnotationProcessor
{
   // 
}
```
### 定义注解器
- 说明
> 定义注解器时，必须为其绑定注解处理器  
> 可通过"hehe\core\hcontainer\annotation\Annotation"注解器将"注解器"与"注解处理器"绑定  
> Annotation格式:@Annotation("注解处理器类路径")  

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
> 原生注解与注释注解的用户基本一致,格式如下  
> #[注解器("第一个构造参数值")]  
> #[注解器("第一个构造参数值",属性名称1:属性值1,属性名称2:属性值2)]  
> #[注解器(属性名称1:属性值1,属性名称2:属性值2)]  
> #[注解器(array(属性名称1=>属性值1,属性名称2=>属性值2))]

- 定义原生注解器

```php
namespace hehe\core\hcontainer\annotation;

use hehe\core\hcontainer\ann\base\BaseAnnotation;
use hehe\core\hcontainer\annotation\Annotation;
use Attribute;

#[Annotation("hehe\core\hcontainer\annotation\BeanProcessor")]
#[Attribute]
class Ref extends BaseAnnotation
{
    public $ref;

    public function __construct($value = null,bool $lazy = null,string $ref = null)
    {
        // 无需处理构造参数,调用injectArgParams将构造参数直接赋值给注解器属性
        $this->injectArgParams(func_get_args(),'ref');
    }
}

```

- 原生注解器使用示例
```php
namespace admin\service;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\annotation\Ref;

#[Bean("user")]
class User
{
    /**
     * role值为Role bean对象
     */
     #[Ref("role",_lazy:true)]
    public $role;
    
    #[After("hcontainer\\tests\common\LogBehavior@@log2")]
    public function ok()
    {
        return 'ok';
    }
}

```

### 获取注解对象
```php
use hehe\core\hcontainer\ContainerManager;
$hcontainer = new ContainerManager();

// 查找UserLog类的Bean注解
$annBeans = $hcontainer->getAnnManager()->findClassAnnotations(UserLog::class,Bean::class);
// 判断UserLog类是否存在Bean注解
$hasStatus = $hcontainer->getAnnManager()->hasClassAnnotations(UserLog::class,Bean::class);

// 查找UserBean类方法(doAfter)的After注解
$annBean = $hcontainer->getAnnManager()->findMethodAnnotations(UserBean::class,'doAfter',After::class);
// 判断UserBean类方法(doAfter)是否存在After注解
$hcontainer->getAnnManager()->hasMethodAnnotations(UserBean::class,'doAfter',After::class);

// 查找UserBean类属性(annRole)的Ref注解
$annBean = $hcontainer->getAnnManager()->findPropertyAnnotations(UserBean::class,'annRole',Ref::class);
// 判断UserBean类属性(annRole)是否存在Ref注解
$hcontainer->getAnnManager()->hasPropertyAnnotations(UserBean::class,'annRole',Ref::class);


```

### 默认注解器列表

- Bean注解
> 标识此类为bean对象  
> 类路径:hehe\core\hcontainer\annotation\Bean  

```php
use hehe\core\hcontainer\annotation\Bean;
/**
 * @Bean("user")
 * @Bean("user",_onProxy=>true)
 * @Bean(_id="user")
 */
class User
{

}
```

- Proxy注解
> 标识此类启用了代理,同时会生成代理对象,一般用于切面  
> 类路径:hehe\core\hcontainer\annotation\Proxy  
```php
use hehe\core\hcontainer\annotation\Proxy;
/**
 * @Proxy()
 */
class User
{

}
```

- Ref注解
> 标识类属性为bean对象  
> 类路径:hehe\core\hcontainer\annotation\Ref  
```php
use hehe\core\hcontainer\annotation\Ref;

class User
{   
    /**
    * @Ref('role'),
    * @Ref('role',_lazy=true)
    */
    protected $role;
}
```

- After注解
> 标识aop切面,在目标方法执行之后切入“业务行为”  
> 类路径:hehe\core\hcontainer\aop\annotation\After  
> 示例:@After("业务行为类路径"),@After("业务行为类路径@方法"),@After("业务行为类路径@@静态方法")

```php
use hehe\core\hcontainer\aop\annotation\After;

class User
{   
    /**
     * 注册用户
     * @After("hcontainer\tests\common\LogBehavior")
     */
    public function add(array $user)
    {
        return 'ok';
    }
}
```

- Before注解
> 标识aop切面,在目标方法执行之前切入“业务行为”  
> 类路径:hehe\core\hcontainer\aop\annotation\Before
> 示例:与@After格式一致

- Around注解
> 标识aop切面,在目标方法执行之前与之后切入“业务行为”  
> 类路径:hehe\core\hcontainer\aop\annotation\Around  
> 示例:与@After格式一致  

- AfterThrowing注解
> 标识aop切面,在目标方法执行抛出异常时切入“业务行为”  
> 类路径:hehe\core\hcontainer\aop\annotation\AfterThrowing  
> 示例:与@After格式一致

- AfterReturning注解
> aop切面注解器,用于在执行目标方法之后,无论是否发生异常,都会切入“业务行为”,类似异常的finally  
> 类路径:hehe\core\hcontainer\aop\annotation\AfterReturning  
> 示例:与@After格式一致









