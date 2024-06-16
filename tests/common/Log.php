<?php
namespace hcontainer\tests\common;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\aop\annotation\After;

/**
 *
 * Class UserBean
 * @package hcontainer\tests\common
 * @Bean("log")
 * @After("hcontainer\tests\common\LogBehavior@log",pointcut=".+Action")
 */
class Log extends BaseLog
{
    public $attr_ok;
    public $attr_ok1;

    /**
     * @var UserBean
     */
    public $user;

    public function __construct(UserBean $user)
    {
        $this->user = $user;
    }

    public function ok($msg)
    {

        return $msg;
    }

}
