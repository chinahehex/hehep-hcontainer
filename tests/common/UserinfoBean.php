<?php
namespace hcontainer\tests\common;

/**
 *
 * Class UserBean
 * @package hcontainer\tests\common
 */
class UserinfoBean
{
    /**
     * 姓名
     * @var
     */
    public $name;

    /**
     * 构造函数
     * @var RoleBean
     */
    public $structureRole;

    public function __construct($name = '')
    {
        $this->name = $name;
    }

    public function ok()
    {
        return true;
    }

    public function getName()
    {
        return $this->name;
    }

}
