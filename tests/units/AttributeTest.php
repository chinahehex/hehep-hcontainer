<?php
namespace hcontainer\tests\units;
use hcontainer\tests\common\Log;
use hcontainer\tests\common\UserBean;
use hcontainer\tests\common\UserinfoBean;
use hcontainer\tests\TestCase;
use hehe\core\hcontainer\ContainerManager;


class AttributeTest extends TestCase
{
    protected function setUp():void
    {
        parent::setUp();
        $this->register('bean.php');
        $this->hcontainer->addScanRule(TestCase::class,ContainerManager::class)
            ->startScan();

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

    public function testGet()
    {
        if (!$this->checkVersion()) {return;}

        $sysLog = $this->hcontainer->getBean('sysLog');
        $pos = $sysLog->okAction($sysLog,"after");
        $this->assertTrue(($sysLog->aop_log == "log:{$pos}" && $pos == 'after'));


        $pos = $sysLog->okaop($sysLog,"afterok");
        $this->assertTrue(($sysLog->aop_log == "log:{$pos}" && $pos == 'afterok'));

        $pos = $sysLog->okaop1($sysLog,"afterok1");
        $this->assertTrue(($sysLog->aop_log == "log:{$pos}" && $pos == 'afterok1'));

    }

    public function testRef()
    {
        if (!$this->checkVersion()) {return;}
        $sysLog = $this->hcontainer->getBean('sysLog');
        $this->assertTrue($sysLog->annUser instanceof UserBean && $sysLog->annUser->ok());
    }

}
