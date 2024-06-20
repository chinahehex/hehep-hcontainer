<?php
namespace hcontainer\tests\common;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\annotation\Ref;

/**
 *
 * Class UserBean
 * @package hcontainer\tests\common
 * @Bean("role")
 */
class RoleBean
{
    /**
     * @Ref("user")
     * @var UserBean
     */
    public $user;

    public $role_name = '';
    public function __construct()
    {
        $this->role_name = '延迟角色';
    }

    public function ok()
    {
        return true;
    }

    public function lazy($msg)
    {
        return $msg;
    }
}
