<?php

namespace hehe\core\hcontainer\ann\scan;

/**
 * 扫描事件类
 *<B>说明：</B>
 *<pre>
 * 扫描的类文件,又此事件处理,主要功能为收集注解信息
 *</pre>
 */
abstract class ScanHandler
{
    /**
     * 处理clazz 列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $clazzs 扫描到的所有class 类
     */
    abstract  public function handler($clazzs);
}
