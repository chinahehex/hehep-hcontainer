<?php
namespace hcontainer\tests;

use hehe\core\hcontainer\ContainerManager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \hehe\core\hcontainer\ContainerManager
     */
    protected $hcontainer = null;

    // 单个测试之前(每个测试方法之前调用)
    protected function setUp():void
    {
        $this->hcontainer = new ContainerManager();
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown():void
    {

    }

    // 整个测试类之前
    public static function setUpBeforeClass():void
    {

    }

    // 整个测试类之前
    public static function tearDownAfterClass():void
    {

    }

    protected function checkVersion()
    {
        if ((explode('.',phpversion()))[0] != 8) {
            $this->assertTrue(true);
            return false;
        } else {
            return true;
        }
    }

    public function register($res_path)
    {
        $comm = require __DIR__ . '/res/' . $res_path;

        $this->hcontainer->batchRegister($comm);
    }

}
