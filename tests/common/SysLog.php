<?php
namespace hcontainer\tests\common;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\annotation\Ref;
use hehe\core\hcontainer\aop\annotation\After;

#[Bean("sysLog")]
#[After(behaviors:"hcontainer\\tests\common\LogBehavior@log",pointcut:".+Action")]
class SysLog extends BaseLog
{

    public $aop_log = '';


    #[Ref('user')]
    public $annUser;

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

    #[After(behaviors:"hcontainer\\tests\common\LogBehavior@@log2")]
    public function okaop1($log,$msg)
    {

        return $msg;
    }

    #[After("hcontainer\\tests\common\LogBehavior@log")]
    public function okaop($log,$msg)
    {

        return $msg;
    }

    public function okAction($log,$msg)
    {

        return $msg;
    }

}
