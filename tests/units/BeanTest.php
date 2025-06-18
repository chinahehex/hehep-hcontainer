<?php
namespace hcontainer\tests\units;
use hcontainer\tests\common\Log;
use hcontainer\tests\common\UserBean;
use hcontainer\tests\common\UserinfoBean;
use hcontainer\tests\common\UserLog;
use hcontainer\tests\TestCase;
use hehe\core\hcontainer\annotation\Bean;
use hehe\core\hcontainer\annotation\Ref;
use hehe\core\hcontainer\aop\annotation\After;
use hehe\core\hcontainer\ContainerManager;
use Psr\Log\LoggerInterface;


class BeanTest extends TestCase
{
    protected function setUp():void
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


    public function testBind()
    {
        $beans = [
            'hlog'=>[
                'class'=>'hehe\core\hlogger\LogManager',
                '_bind'=>[LoggerInterface::class]
            ],
        ];

        $this->hcontainer->batchRegister($beans);

        // 通过id 获取bean对象
        $hlog1 = $this->hcontainer->getBean('hlog');

        // 通过接口获取bean对象
        $hlog2 = $this->hcontainer->getBeanByClass(LoggerInterface::class);

        $this->assertSame($hlog1,$hlog2);
    }

    public function testBind2()
    {
        $beans = [
            'hlog'=>[
                'class'=>'hehe\core\hlogger\LogManager',
            ],
        ];

        $this->hcontainer->batchRegister($beans);
        $this->hcontainer->bindBeanClass('hlog',LoggerInterface::class);

        // 通过id 获取bean对象
        $hlog1 = $this->hcontainer->getBean('hlog');

        // 通过接口获取bean对象
        $hlog2 = $this->hcontainer->getBeanByClass(LoggerInterface::class);

        $this->assertSame($hlog1,$hlog2);
    }

    public function testgetAnn()
    {
        $annBean = $this->hcontainer->getAnnManager()->findClassAnnotations(UserLog::class,Bean::class);
        $this->assertTrue($annBean[0] instanceof Bean);
        $this->assertTrue($this->hcontainer->getAnnManager()->hasClassAnnotations(UserLog::class,Bean::class));

        $annBean = $this->hcontainer->getAnnManager()->findMethodAnnotations(UserBean::class,'doAfter',After::class);
        $this->assertTrue($annBean[0] instanceof After);
        $this->assertTrue($this->hcontainer->getAnnManager()->hasMethodAnnotations(UserBean::class,'doAfter',After::class));

        $annBean = $this->hcontainer->getAnnManager()->findPropertyAnnotations(UserBean::class,'annRole',Ref::class);
        $this->assertTrue($annBean[0] instanceof Ref);
        $this->assertTrue($this->hcontainer->getAnnManager()->hasPropertyAnnotations(UserBean::class,'annRole',Ref::class));

    }

}
