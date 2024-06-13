<?php
namespace hehe\core\hcontainer\base;

/**
 * 容器类
 *<B>说明：</B>
 *<pre>
 * 为类提供单例读取操作
 *</pre>
 */
class Container
{

    /**
     * 对象作用域
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    public $scope = '';

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
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $scope 作用范围
     */
    public function __construct(string $scope = '')
    {
        $this->scope = $scope;
    }

    /**
     * bean 是否存在
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $beanId
     * @return boolean
     */
    public function hasBean(string $beanId):bool
    {
        if (isset($this->beans[$beanId])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取 bean 对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $beanId
     * @return Object
     */
    public function getBean(string $beanId)
    {
        // 单例
        if (isset($this->beans[$beanId])) {
            return $this->beans[$beanId];
        } else {
            return null;
        }
    }

    /**
     * 设置 bean 对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $beanId
     * @param object $bean
     */
    public function setBean(string $beanId,$bean):void
    {
        $this->beans[$beanId] = $bean;
    }

}
