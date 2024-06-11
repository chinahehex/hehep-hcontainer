# hehep-hannotation

## 介绍
- hehep-hannotation 是一个PHP 注解工具组件
- 收集注解元数据
- 提供注解处理器
- 支持自定义扫描规则
- 基本概念:注解器,注解处理器


## 安装
- 直接下载:
```

```
- 命令安装：
```
composer require hehep-hannotation
```

## 组件配置
```php
$config = [
    // 扫描规则
    'scanRules'=>['扫描的命名空间','命名空间对应的路径','class'=>'扫描类路径'],
    // 优先注解器
    'firstProcessors'=>['优先处理的注解处理器类路径1','优先处理的注解处理器类路径1'],
    // 用于替换旧的处理器
    'customProcessors'=>['旧注解处理器'=>'新注解处理器'],
]; 
```

## 创建注解处理器
```php
namespace hehe\core\hvalidation\annotation;

use hehe\core\hannotation\base\AnnotationProcessor;
use hehe\core\hvalidation\Validation;


class AnnValidatorProcessor extends AnnotationProcessor
{
    /**
     * 验证器规则列表
     * @var array
     */
    protected $validators = [];

    /**
     * 注解转换成验证规则
     * @param object $annotation
     */
    protected function toValidatRule($annotation)
    {
        // 获取注解器的所有属性值
        $annotationAttrs = $this->getAttribute($annotation);
        $validatorRule = [];

        $validatorRule[0] = $annotationAttrs['name'];
        $validatorRule[1] = $annotationAttrs['validator'];
        if ($annotationAttrs['on'] !== null) {
            $validatorRule['on'] = $annotationAttrs['on'];
        }

        if ($annotationAttrs['goon'] !== null) {
            $validatorRule['goon'] = $annotationAttrs['goon'];
        }

        return $validatorRule;
    }
    
    // 类注解处理方法
    protected function annotationHandlerClazz($annotation,$clazz)
    {
        // 处理注解
    }

    // 类属性注解处理方法
    protected function annotationHandlerAttribute($annotation,$clazz,$attribute)
    {
        $validatorRule = $this->toValidatRule($annotation);
        $validatorRule[0] = $attribute;

        $this->validators[$clazz][] = $validatorRule;
    }
    
    // 类方法注解处理方法
    protected function annotationHandlerMethod($annotation,$clazz,$method)
    {
        $validatorRule = $this->toValidatRule($annotation);
        $this->validators[$clazz .'@'.$method][] = $validatorRule;
    }
    
    // 重写获取注解方法
    public function getAnnotationors(string $class_key = '')
    {
        if (isset($this->validators[$class_key])) {
            return $this->validators[$class_key];
        } else {
            return [];
        }
    }
    
    // 扫描结束触发事件
    public function endScanHandle()
    {
    
    }
}
```


## 创建注解器

- 创建验证注解器
- 注解器必须与注解处理器绑定,通过@Annotation 绑定关系
```php
namespace hehe\core\hvalidation\annotation;

use  hehe\core\hannotation\base\Annotation;

/**
 * @Annotation("hehe\core\hvalidation\annotation\AnnValidatorProcessor")
 */
class AnnValidator
{

    public $validator = [];

    public $message;

    public $on;

    public $goon;

    /**
     * 验证的键名
     * @var string
     */
    public $name;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $attrs
     */
    public function __construct($attrs = [])
    {
        // Required
        foreach ($attrs as $attr=>$value) {
            if ($attr == "value") {
                $this->message = $value;
            } else {
                if (property_exists($this,$attr)) {
                    $this->$attr = $value;
                } else {
                    $this->validator[$attr] = $value;
                }
            }

            $this->validator['message'] = $this->message;
        }
    }
}

```

- 创建必填验证器
```php
namespace hehe\core\hvalidation\annotation;

use  hehe\core\hannotation\base\Annotation;

/**
 * @Annotation("hehe\core\hvalidation\annotation\AnnValidatorProcessor")
 */
class RequiredValid extends AnnValidator
{
    public function __construct($attrs = [])
    {
        parent::__construct($attrs);
        $this->validator[0] = 'required';
    }
}
```

## 注解器使用
```
注解器设置值时不能使用单引号,比如@RequiredValid('不能为空')这种方式是错误的,必须使用双引号@RequiredValid("不能为空")
```

- 注解器在控制器中的用法
```php
/**
*  控制器
 */
class TestController extends WebController
{
    /**
	 * @var string
	 * @RequiredValid("不能为空")
	 */
	public $name;
	
	/**
	 * @var string
	 * @RequiredValid("不能为空3",name="name")
	 */
	public function annAction()
	{
	
	}
}
```
## 基本示例

```php
namespace hehe\core\hannotation\annotation;
use hehe\core\hannotation\AnnotationManager;
use hehe\core\hcontainer\annotation\BeanProcessor;

$hvalidation_config = [
    'scan_rule'=>['apiadmin']
];

$hvalidation = new AnnotationManager($hvalidation_config);

// 添加扫描规则
$hvalidation->addScanRule(['apiadmin','/work/apiadmin']);

// 添加扫描结束优先的注解处理器
$hvalidation->addFirstProcessor(BeanProcessor::class);

//$hvalidation->registerCustomProcessors([]);

// 开始扫描
$hvalidation->run();

```
