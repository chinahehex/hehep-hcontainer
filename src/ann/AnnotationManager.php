<?php
namespace hehe\core\hcontainer\ann;

use hehe\core\hcontainer\ann\base\AnnotationParser;
use hehe\core\hcontainer\ann\base\AnnotationProcessor;
use hehe\core\hcontainer\ann\scan\Scan;

/**
 * 注解管理
 *<B>说明：</B>
 *<pre>
 * 容器应用基本流程：扫描指定的目录,收集注解信息,注解信息注入容器
 *</pre>
 */
class AnnotationManager
{
    /**
     * 扫描器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Scan
     */
    protected $scan = null;

    /**
     * 扫描规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $scanRules = [];

    /**
     * 扫描收集的处理器对象集合
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Processor[]|AnnotationProcessor[]
     */
    protected $processorList = [];

    /**
     * 自定义处理器集合
     *<B>说明：</B>
     *<pre>
     *  由于替换旧的注解处理器
     *</pre>
     * @var array<旧注解器类,新注解处理器类>
     */
    public $customProcessors = [];

    /**
     * 优先处理的注解处理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array<注解类处理器>
     */
    public $firstProcessors = [];

    /**
     * 容器管理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var ContainerManager
     */
    protected $containerManager = null;

    public function __construct($attrs = [])
    {
        $this->_init($attrs);
    }

    protected function _init($attrs)
    {
        if (!empty($attrs)) {
            foreach ($attrs as $attr=>$value) {
                $this->$attr = $value;
            }
        }
    }

    /**
     * 开始扫描
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public function start()
    {

        // 开始扫描文件
        $this->startScan();

        // 应用结束
        $this->endScan();

        return $this;
    }

    /**
     * 扫描文件
     *<B>说明：</B>
     *<pre>
     *  收集bean注解
     *</pre>
     */
    protected function startScan()
    {
        $this->scan = new Scan($this->getFormatScanrules());
        $classList = $this->scan->startScanClass();

        $anotationParser = new annotationParser($this);
        $anotationParser->parse($classList);
    }

    protected function getFormatScanrules()
    {
        $scan_rules =  [];
        foreach ($this->scanRules as $rule) {
            list($namespace,$path) = $rule;
            $scan_rule = [
                'path'=>$path,
                'namespace'=>$namespace
            ];

            if (isset($rule['class'])) {
                $scan_rule['class'] = $rule['class'];
            }

            $scan_rules[] = $scan_rule;
        }

        return $scan_rules;
    }

    /**
     * 添加扫描规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array<命名空间,命名空间文件路径> $scan_paths 扫描路径
     * @return static
     */
    public function addScanRule(...$scanPaths):self
    {
        foreach ($scanPaths as $scan_path) {
            if (is_string($scan_path)) {
                $this->scanRules[] = $this->getClassPath($scan_path);
            } else {
                $this->scanRules[] = $scan_path;
            }
        }

        return $this;
    }

    protected function getClassPath($className):array
    {
        $reflection = new \ReflectionClass($className);
        $filename = $reflection->getFileName();
        $namespace = $reflection->getNamespaceName();

        return [$namespace, dirname($filename)];
    }

    /**
     * 添加优先扫描规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array<命名空间,命名空间文件路径> $scanPaths 扫描路径
     * @return static
     */
    public function addFirstScanRule(...$scanPaths):self
    {

        $scanRuleList = [];
        foreach ($scanPaths as $scan_path) {
            if (is_string($scan_path)) {
                $scanRuleList[] = $this->getClassPath($scan_path);
            } else {
                $scanRuleList[] = $scan_path;
            }
        }

        array_unshift($this->scanRules, ...$scanRuleList);

        return $this;
    }


    /**
     * 注册处理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Processor $processor
     */
    public function addProcessor($processor)
    {
        $processorClazz = get_class($processor);
        if (!isset($this->processorList[$processorClazz])) {
            $this->processorList[$processorClazz] = $processor;
        }
    }

    public function addFirstProcessor(...$processors)
    {
        array_unshift($this->firstProcessors, ... $processors);
    }

    public function addCustomProcessors(...$customProcessors)
    {
        $this->customProcessors = $customProcessors + $this->customProcessors;
    }

    /**
     * 获取处理器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $processorClazz
     * @return Processor
     */
    public function getProcessor($processorClazz)
    {
        return $this->processorList[$processorClazz] ??  null;
    }

    /**
     * 处理器对象是否存在
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $processorClazz
     * @return Processor
     */
    public function hasProcessor($processorClazz)
    {
        return isset($this->processorList[$processorClazz]) ?  true : false;
    }

    /**
     * 创建处理器,并保存
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $processorClazz
     * @return Processor
     */
    public function makeProcessor($processorClazz)
    {
        /**@var Processor $processor */
        if (isset($this->customProcessors[$processorClazz])) {
            $custom_processor_class = $this->customProcessors[$processorClazz];
            $processor = new $custom_processor_class($this->containerManager);
            $this->processorList[$processorClazz] = $processor;
        } else {
            $processor = new $processorClazz($this->containerManager);
            $this->processorList[$processorClazz] = $processor;
        }

        return $processor;
    }



    /**
     * 扫描接触,触发事件
     *<B>说明：</B>
     *<pre>
     *  触发处理器结束工作
     *</pre>
     */
    protected function endScan()
    {
        // 优先处理器排前面
        $_firstProcessors = [];
        foreach ($this->firstProcessors as $processor_class) {
            if (isset($this->processorList[$processor_class])) {
                $_firstProcessors[$processor_class] = $this->processorList[$processor_class];
            }
        }

        $this->processorList = $_firstProcessors + $this->processorList;

        foreach ($this->processorList as $processor) {
            $processor->endScanHandle();
        }
    }
}
