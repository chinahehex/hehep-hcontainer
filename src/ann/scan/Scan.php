<?php
namespace hehe\core\hcontainer\ann\scan;

/**
 * 扫描类
 *<B>说明：</B>
 *<pre>
 * 扫描项目所有php文件,获取注解基本信息
 *</pre>
 */
class Scan
{
    /**
     * 扫描默认路径
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $defautPath = '';

    /**
     * 扫描路径列表
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var array
     */
    protected $scanRules = [];

    /**
     * 扫描规则配置对象列表
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var ScanRule[]
     */
    protected $scanRuleList = [];

    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     *
     */
    public function __construct(array $scanRules)
    {
        $this->scanRules = $scanRules;

        $this->_init();
    }

    /**
     * 初始化
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function _init()
    {
        foreach ($this->scanRules as $rule) {
            $ruleClazz = ScanRule::class;
            if (isset($rule['class'])) {
                $ruleClazz = $rule['class'];
                unset($rule['class']);
            }

            $this->scanRuleList[] = new $ruleClazz($rule);
        }

        if (empty($this->scanRuleList) && !empty($this->defautPath)) {
            $this->scanRuleList[] = $this->createDefaultScanRule([
                'path'=>$this->defautPath
            ]);
        }
    }

    /**
     * 创建默认扫描规则对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $attrs
     * @return ScanRule
     */
    protected function createDefaultScanRule($attrs)
    {
        if (!isset($attrs['rule'])) {
            $attrs['rule'] = '.php';
        }

        return new ScanRule($attrs);
    }

    /**
     * 扫描类文件统一入口
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return array 类文件路径列表
     */
    public function startScanClass()
    {
        $fileClazzList = [];
        foreach ($this->scanRuleList as $scanRule) {
            $this->doScanResource($scanRule,'',$fileClazzList);
        }


        return $fileClazzList;
    }

    /**
     * 按规则扫描类文件
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param ScanRule $scanRule
     * @return array 类文件路径列表
     */
    public function scanClassByRule($scanRule)
    {
        $fileClazzList = [];
        $this->doScanResource($scanRule,'',$fileClazzList);

        return $fileClazzList;
    }

    /**
     * 按指定目录扫描类文件
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $basePath
     * @param string $baseNamespace
     * @return array 类文件路径列表
     */
    public function scanClassByPath($basePath,$baseNamespace)
    {
        $scanRule = $this->createDefaultScanRule([
            'path'=>$basePath,
            'namespace'=>$baseNamespace
        ]);

        $fileClazzList = [];
        $this->doScanResource($scanRule,'',$fileClazzList);

        return $fileClazzList;
    }

    /**
     * 递归扫描目录
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param ScanRule $scanRule 扫描规则
     * @param string $dirName 读取的目录名称
     * @param array $fileClazzList 类文件列表
     */
    protected function doScanResource($scanRule,$dirName = '',&$fileClazzList)
    {
        if (!empty($dirName)) {
            $baseResourcePath = $scanRule->getBasePath() . DIRECTORY_SEPARATOR . $dirName;
        } else {
            $baseResourcePath = $scanRule->getBasePath() ;
        }

        if (empty($baseResourcePath)) {
            return ;
        }

        $fileList = scandir($baseResourcePath);
        foreach ($fileList as $filename) {
            if ($filename === '.' || $filename === '..'){
                continue;
            }

            $filePath = $baseResourcePath . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($filePath)) {
                // 继续扫描
                $this->doScanResource($scanRule,!empty($dirName) ? $dirName . DIRECTORY_SEPARATOR . $filename : $filename,$fileClazzList);
            } else {
                if ($scanRule->check($filePath)) {
                    $classNamespace = $scanRule->getBaseNamespace() . '\\' . str_replace(DIRECTORY_SEPARATOR,'\\',$dirName) . '\\' . basename($filename,".php");;
                    if (!empty($dirName)) {
                        $classNamespace = $scanRule->getBaseNamespace() . '\\' . str_replace(DIRECTORY_SEPARATOR,'\\',$dirName) . '\\' . basename($filename,".php");;
                    } else {
                        $classNamespace = $scanRule->getBaseNamespace() . '\\' . basename($filename,".php");;
                    }
                    if (class_exists($classNamespace) || interface_exists($classNamespace)) {
                        $fileClazzList[] = $classNamespace;
                    }
                }
            }
        }
    }


}
