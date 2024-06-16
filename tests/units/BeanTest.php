<?php
namespace hcontainer\tests\units;
use hcontainer\tests\common\Log;
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

        $userinfo = $this->hcontainer->make('userinfo',["name"=>'hehex']);
        $this->assertTrue($userinfo->getName() == 'hehex');
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

    public function testArgsBean()
    {
        /** @var UserBean $user**/
        $user = $this->hcontainer->getBean('user');
        $this->assertTrue($user->argRole->ok());
    }

    public function testLazy()
    {
        $user = $this->hcontainer->getBean('user');
        $this->assertTrue($user->argRole->lazy("lazy") == 'lazy');

        $this->assertTrue($user->argRole->role_name == '延迟角色');

        $user->argRole->role_name = '难搞';
        $this->assertTrue($user->argRole->role_name == '难搞');

        unset($user->argRole->role_name);
        $this->assertTrue(!isset($user->argRole->role_name));

    }

    public function testproxy()
    {

        $log = $this->hcontainer->getBean('log');
        $this->assertTrue($log->ok("msg") == "msg");


        $user = $this->hcontainer->getBean('user');

        $this->assertTrue(spl_object_hash($user)==spl_object_hash($log->user));
    }

    public function testProxyLazy()
    {

        $user = $this->hcontainer->getBean('user');
        $userLog = $this->hcontainer->getBean('userLog');


        $this->assertTrue(spl_object_hash($user->userLog)==spl_object_hash($userLog));
    }

    public function testAnnBean()
    {
        if (explode('.',phpversion()) != 8) {
            $this->assertTrue(true);
            return;
        }

        $sysLog = $this->hcontainer->getBean('sysLog');
        $this->assertTrue($sysLog->ok("hehex") == 'hehex');
    }

    public function testAnnAfter()
    {
        if (explode('.',phpversion()) != 8) {
            $this->assertTrue(true);
            return;
        }

        $sysLog = $this->hcontainer->getBean('sysLog');
        $this->assertTrue($sysLog->okAction("hehex") == 'hehex');
    }

}
