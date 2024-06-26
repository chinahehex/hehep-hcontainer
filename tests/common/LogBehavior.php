<?php
namespace hcontainer\tests\common;
use hehe\core\hcontainer\aop\base\AopBehavior;
use hehe\core\hcontainer\aop\base\PointcutContext;

class LogBehavior extends AopBehavior
{
    public function handle(PointcutContext $pointcutCtx)
    {
        $user = $pointcutCtx->parameters[0];
        /** @var \Exception $t **/
        if (!is_null($pointcutCtx->exception)) {
            $user->aop_log = "log:" . $pointcutCtx->exception->getMessage();
        } else {
            $user->aop_log = "log:" . $pointcutCtx->methodResult;
        }
        var_dump("handle log");
    }

    public function log(PointcutContext $pointcutCtx)
    {
        $user = $pointcutCtx->parameters[0];
        /** @var \Exception $t **/
        if (!is_null($pointcutCtx->exception)) {
            $user->aop_log = "log:" . $pointcutCtx->exception->getMessage();
        } else {
            $user->aop_log = "log:" . $pointcutCtx->methodResult;
        }
        var_dump("class log");
    }

    public static function log2(PointcutContext $pointcutCtx)
    {
        $user = $pointcutCtx->parameters[0];
        /** @var \Exception $t **/
        if (!is_null($pointcutCtx->exception)) {
            $user->aop_log = "log:" . $pointcutCtx->exception->getMessage();
        } else {
            $user->aop_log = "log:" . $pointcutCtx->methodResult;
        }
        var_dump("static log");
    }
}
