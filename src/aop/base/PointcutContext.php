<?php
namespace hehe\core\hcontainer\aop\base;

/**
 * 拦截点的上下文
 * Class PointcutContext
 * @package vendor\hehe\core\hcontainer\aop\base
 */
class PointcutContext
{
    /**
     * 目标类
     * @var
     */
    public $target;

    /**
     * 操作方法名
     * @var string
     */
    public $method;

    /**
     * 操作方法参数
     * @var array
     */
    public $parameters;

    /**
     * 执行的通知点名称
     * @var string
     */
    public $advice;

    /**
     * 方法执行返回结果
     * @var mixed
     */
    public $methodResult;

    /**
     * 异常
     * @var \Throwable
     */
    public $exception;
}
