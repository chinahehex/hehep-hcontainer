<?php
namespace hcontainer\tests\units;
use hcontainer\tests\common\UserBean;
use hcontainer\tests\TestCase;
use hehe\core\hcontainer\ContainerManager;


/**
 * Class AopTest
 * @package hcontainer\tests\units
 */
class AopTest extends TestCase
{
    protected function setUp():void
    {
        parent::setUp();
        $this->register('bean.php');
        $this->hcontainer->addScanRule(TestCase::class,ContainerManager::class)
            ->startScan();

    }

    public function testAfter()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $pos = $user->doAfter($user,'after');

        $this->assertTrue(($user->aop_log == "log:{$pos}" && $pos == 'after'));
    }

    public function testBefore()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $pos = $user->doBefore($user,'before');
        $this->assertTrue(($user->aop_log == "log:" && $pos == 'before'));
    }

    public function testAround()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $pos = $user->doAround($user,'around');
        $this->assertTrue(($user->aop_log == "log:{$pos}" && $pos == 'around'));
    }

    public function testAfterThrowing()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        try {
            $pos = $user->doAfterThrowing($user,'AfterThrowing');
        } catch (\Exception $e) {
            $pos = $e->getMessage();
        }

        $this->assertTrue(($user->aop_log == "log:{$pos}" && $pos == 'AfterThrowing'));
    }

    public function testNewMethod()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $pos = $user->doNewMethod($user,'after');

        $this->assertTrue(($user->aop_log == "log:{$pos}" && $pos == 'after'));
    }

    public function testNew2Method()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $pos = $user->doNew2Method($user,'after');

        $this->assertTrue(($user->aop_log == "log:{$pos}" && $pos == 'after'));
    }

    public function testDo1Action()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $pos = $user->do1Action($user,'after');

        $this->assertTrue(($user->aop_log == "log:{$pos}" && $pos == 'after'));
    }

    public function testDo2Action()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $pos = $user->do2Action($user,'after');

        $this->assertTrue(($user->aop_log == "log:{$pos}" && $pos == 'after'));
    }


}
