<?php
namespace hcontainer\tests\units;
use hcontainer\tests\common\UserBean;
use hcontainer\tests\common\UserinfoBean;
use hcontainer\tests\TestCase;
use hehe\core\hcontainer\ContainerManager;


class BeanTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->register('bean.php');
        $this->hcontainer->addScanRule(TestCase::class,ContainerManager::class)
            ->startScan();

    }

    public function testRegister()
    {
        $this->hcontainer->register('userinfo',UserinfoBean::class);
        $userinfo = $this->hcontainer->getBean('userinfo');
        $this->assertTrue($userinfo instanceof UserinfoBean);
    }

    public function testBatchRegister()
    {
        $beans = [
            'userinfo'=>['class'=>UserinfoBean::class]
        ];
        $this->hcontainer->batchRegister($beans);
        $userinfo = $this->hcontainer->getBean('userinfo');
        $this->assertTrue($userinfo instanceof UserinfoBean);
    }

    // 提供构造参数
    public function testBeanArgs()
    {
        $this->hcontainer->register('userinfo',UserinfoBean::class);
        /** @var UserinfoBean $userinfo**/
        $userinfo = $this->hcontainer->make('userinfo',["hehe"]);
        $this->assertTrue($userinfo->getName() == 'hehe');
    }

    // 获取一个单利对象
    public function testGet()
    {
        // 获取一个bean
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $this->assertTrue($user->ok());

        $user1 = $this->hcontainer->getBean('user');

        $this->assertTrue(spl_object_hash($user)==spl_object_hash($user1));
    }

    // 创建对象
    public function testMake()
    {
        /** @var UserBean $user1 **/
        $user1 = $this->hcontainer->getBean('user');
        $user2 = $this->hcontainer->make('user');

        $this->assertTrue(spl_object_hash($user1)!=spl_object_hash($user2));
    }

    public function testRef()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $this->assertTrue($user->refRole->ok());
    }

    public function testAnn()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $this->assertTrue($user->annRole->ok());
    }
}
