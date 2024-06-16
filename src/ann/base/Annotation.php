<?php
namespace hehe\core\hcontainer\ann\base;
use Attribute;

/**
 * Annotation 元注解类
 *<B>说明：</B>
 *<pre>
 * @Annotation 有此标志,说明是注解类
 * 元注解解释:注解的注解简称为元注解
 *</pre>
 */
#[Attribute]
class Annotation extends Ann
{
    const TARGET_CLASS = 'CLASS';

    const TARGET_METHOD = 'METHOD';

    const TARGET_FIELD = 'FIELD';

    /**
     * 处理器类路径
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    public $processor = '';

    /**
     * 作用范围
     *<B>说明：</B>
     *<pre>
     *  CLASS,METHOD
     *</pre>
     * @var string
     */
    public $target;

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param  array $value
     */
    public function __construct($value,string $target = null,string $processor = null)
    {
        $this->injectArgParams(func_get_args(),'processor');
    }

    /**
     * 获取注解处理器类名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return string
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * 是否有效范围
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $target
     * @return boolean
     */
    public function effectiveTarget($target)
    {
        $target = strtoupper($target);

        if (empty($this->target)) {
            return true;
        }

        if (!is_array($this->target)) {
            $this->target = explode(',',$this->target);
        }

        if (in_array($target,$this->target)) {
            return true;
        }

        return false;
    }
}
