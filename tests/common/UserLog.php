<?php
namespace hcontainer\tests\common;

use hehe\core\hcontainer\annotation\Bean;

/**
 * @Bean("userLog")
 */
class UserLog extends BaseLog
{

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
