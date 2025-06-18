<?php
namespace hehe\core\hcontainer\ann;

use hehe\core\hcontainer\ann\base\AnnotationParser;
use hehe\core\hcontainer\ann\base\AnnotationProcessor;
use hehe\core\hcontainer\ann\rule\Scan;
use hehe\core\hcontainer\ContainerManager;

/**
 * 注解管理器
 *<B>说明：</B>
 *<pre>
 * 控制扫描的基本流程：指定扫描目录,收集注解信息,注解信息处理
 *</pre>
 */
class AnnManager
{
    /**
     * 扫描器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Scan
     */
    protected $scan;

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
     * @var AnnotationProcessor[]
     */
    protected $processorList = [];

    /**
     * 重写处理器集合
     *<B>说明：</B>
     *<pre>
     *  用新注解处理替换旧的注解处理器
     *</pre>
     * @var array<旧注解器类,新注解处理器类>
     */
    protected $customProcessors = [];

    /**
     * 优先处理的注解处理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array<注解类处理器>
     */
    protected $firstProcessors = [];

    /**
     * 容器管理器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var ContainerManager
     */
    protected $containerManager;

    /**
     * @var AnnotationParser
     */
    protected $anotationParser;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }

        $this->anotationParser = new AnnotationParser($this);
    }

    /**
     * 开始扫描
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return static
     */
    public function start():self
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
    protected function startScan():void
    {
        $this->scan = new Scan($this->getFormatScanRules());
        $classList = $this->scan->startScanClass();
        $this->anotationParser->parse($classList);
    }

    public function findClassAnnotations(string $class, string $targetAnnotation):array
    {
        return $this->anotationParser->findClassAnnotations($class,$targetAnnotation);
    }

    public function hasClassAnnotations(string $class, string $targetAnnotation):bool
    {
        return count($this->anotationParser->findClassAnnotations($class,$targetAnnotation)) > 0;
    }

    public function findMethodAnnotations(string $class, string $method, string $targetAnnotation):array
    {
        return $this->anotationParser->findMethodAnnotations($class,$method,$targetAnnotation);
    }

    public function hasMethodAnnotations(string $class, string $method, string $targetAnnotation):bool
    {
        return count($this->anotationParser->findMethodAnnotations($class,$method,$targetAnnotation)) > 0;
    }

    public function findPropertyAnnotations(string $class, string $property, string $targetAnnotation):array
    {
        return $this->anotationParser->findPropertyAnnotations($class,$property,$targetAnnotation);
    }

    public function hasPropertyAnnotations(string $class, string $property, string $targetAnnotation):bool
    {
        return count($this->anotationParser->findPropertyAnnotations($class,$property,$targetAnnotation)) > 0;
    }

    protected function getFormatScanRules():array
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
     * @param array<命名空间,命名空间文件路径> $scanRules 扫描路径
     * @return static
     */
    public function addScanRule(...$scanRules):self
    {
        foreach ($scanRules as $scan_rule) {
            if (is_string($scan_rule)) {
                $this->scanRules[] = $this->getClassPath($scan_rule);
            } else {
                $this->scanRules[] = $scan_rule;
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
     * @param array<命名空间,命名空间文件路径> $scanRules 扫描路径
     * @return static
     */
    public function addFirstScanRule(...$scanRules):self
    {

        $scanRuleList = [];
        foreach ($scanRules as $scan_rule) {
            if (is_string($scan_rule)) {
                $scanRuleList[] = $this->getClassPath($scan_rule);
            } else {
                $scanRuleList[] = $scan_rule;
            }
        }

        array_unshift($this->scanRules, ...$scanRuleList);

        return $this;
    }

    public function addFirstProcessor(...$processors):self
    {
        array_unshift($this->firstProcessors, ... $processors);

        return $this;
    }

    public function addCustomProcessors(...$customProcessors):self
    {
        $this->customProcessors = $customProcessors + $this->customProcessors;

        return $this;
    }

    /**
     * 获取处理器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $processorClazz
     * @return AnnotationProcessor
     */
    public function getProcessor(string $processorClazz):?AnnotationProcessor
    {
        if (!$this->hasProcessor($processorClazz)) {
            $this->processorList[$processorClazz] = $this->makeProcessor($processorClazz);
        }

        return $this->processorList[$processorClazz];
    }

    /**
     * 处理器对象是否存在
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $processorClazz
     * @return bool
     */
    public function hasProcessor($processorClazz):bool
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
     * @return AnnotationProcessor
     */
    protected function makeProcessor($processorClazz):AnnotationProcessor
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
    protected function endScan():void
    {
        // 优先处理器排前面
        $_firstProcessors = [];
        foreach ($this->firstProcessors as $processor_class) {
            if (isset($this->processorList[$processor_class])) {
                $_firstProcessors[$processor_class] = $this->processorList[$processor_class];
            }
        }

        $processorList = $_firstProcessors + $this->processorList;

        foreach ($processorList as $processor) {
            if (method_exists($processor,'handleProcessor')) {
                $processor->handleProcessor();
            } else if (method_exists($processor,'handleScanFinish')) {
                $processor->handleScanFinish();
            }
        }

        // 清空变量,回收资源
        $this->firstProcessors = [];
        $this->customProcessors = [];
    }
}
