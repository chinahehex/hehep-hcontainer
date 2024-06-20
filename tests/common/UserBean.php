<?php
namespace hcontainer\tests\common;
use hehe\core\hcontainer\annotation\Ref;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\aop\annotation\Advice;
use hehe\core\hcontainer\aop\annotation\After;
use hehe\core\hcontainer\aop\annotation\Before;
use hehe\core\hcontainer\aop\annotation\Around;
use hehe\core\hcontainer\aop\annotation\AfterThrowing;
use PHPUnit\Runner\Exception;

/**
 *
 * Class UserBean
 * @package hcontainer\tests\common
 * @Bean("user")
 * @After("hcontainer\tests\common\LogBehavior@log",pointcut=".+Action")
 *
 */
#[Bean("user")]
#[After("hcontainer\\tests\common\LogBehavior@log",pointcut:".+Action")]
class UserBean
{
    /**
     * 姓名
     * @var
     */
    public $real_name = '';

    /**
     * 密码
     * @var
     */
    public $pwd;

    /**
     * 注解
     * @Ref("role")
     * @var RoleBean
     */
    public $annRole;

    /**
     * 属性ref 标签
     * @Ref("role")
     * @var RoleBean
     */
    public $refRole;

    /**
     * 构造函数
     * @var RoleBean
     */
    public $argRole;

    public $name;

    public $aop_log = '';

    /**
     * @ref("userLog")
     * @var UserLog
     */
    public $userLog;

    public function __construct($name = '',$argRole = '<ref::role>')
    {
        $this->name = $name;
        $this->argRole = $argRole;
    }

    public function ok()
    {
        return true;
    }

    public function getName():?string
    {
        return $this->name;
    }

    public function getRealname():string
    {
        return $this->real_name;
    }

    public function getPwd()
    {
        return $this->pwd;
    }

    /**
     * 注册用户
     * @After("hcontainer\tests\common\LogBehavior")
     */
    public function doAfter(UserBean $user,string $msg = '')
    {
        return $msg;
    }

    /**
     * 注册用户
     * @Before("hcontainer\tests\common\LogBehavior")
     */
    public function doBefore($user,$msg = '')
    {
        return $msg;
    }

    /**
     * 注册用户
     * @Around("hcontainer\tests\common\LogBehavior")
     */
    public function doAround($user,$msg = '')
    {
        return $msg;
    }

    /**
     * 注册用户
     * @AfterThrowing("hcontainer\tests\common\LogBehavior")
     */
    public function doAfterThrowing($user,$msg = '')
    {
        throw new Exception($msg);
    }

    /**
     * 注册用户
     * @After("hcontainer\tests\common\LogBehavior@log")
     */
    public function doNewMethod($user,$msg = '')
    {
        return $msg;
    }

    /**
     * 注册用户
     * @After("hcontainer\tests\common\LogBehavior@@log2")
     */
    public function doNew2Method($user,$msg = '')
    {
        return $msg;
    }

    public function do1Action(?UserBean $user,$msg = '')
    {
        return $msg;
    }

    public function do2Action(UserBean $user,$msg = '')
    {
        return $msg;
    }

    public function getUser():?RoleBean
    {
        return $this->argRole;
    }

    public function dp3Action(...$users)
    {

    }


}
