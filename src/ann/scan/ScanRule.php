<?php
namespace hehe\core\hcontainer\ann\scan;

/**
 * 扫描规则类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class ScanRule
{
    /**
     * 扫描路径
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $path = '';

    /**
     * 基础命名空间
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $namespace = '';

    /**
     * 扫描规则
     *<B>说明：</B>
     *<pre>
     * 一般为正则
     *</pre>
     * @var string
     */
    protected $rule;

    public function __construct($attrs = [])
    {
        foreach ($attrs as $attr=>$value) {
            $this->$attr = $value;
        }
    }

    public function getBasePath():string
    {
        return $this->path;
    }

    public function getBaseNamespace():string
    {
        return $this->namespace;
    }

    /**
     * 检测类路径是否满足
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $clazzPath
     * @return boolean true 表示满足规则 false 不满足
     */
    public function check($clazzPath):bool
    {
        if ($this->isPhpFile($clazzPath)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 是否php 文件
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $clazzPath
     * @return boolean
     */
    protected function isPhpFile($clazzPath):bool
    {
        $ext = strrchr($clazzPath,'.');
        if ($ext == '.php') {
            return true;
        } else {
            return false;
        }
    }


}
