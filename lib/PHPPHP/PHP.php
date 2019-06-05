<?php

namespace PHPPHP;

use PHPPHP\Engine\Zval;

class PHP {

    protected $executor;

    public function __construct() {
        $functions = new Engine\FunctionStore;
        $constants = new Engine\ConstantStore;
        $classes = new Engine\ClassStore;

        $this->executor = new Engine\Executor($functions, $constants, $classes);

        //输出的
        $this->executor->setOutput(new Engine\Output\Std($this->executor));

        $this->registerExtension(new Engine\CoreExtension);//注册内核的
        $this->registerExtension(new Ext\Strings\Extension);//注册字符串扩展的
    }

    //注册扩展的
    public function registerExtension(Engine\Extension $extension) {
        $this->executor->registerExtension($extension);
    }

    public function registerExtensionByName($name) {
        $class = __NAMESPACE__ . '\Ext\\' . $name . '\Extension';
        if (class_exists($class)) {
            $this->executor->registerExtension(new $class);
        } else {
            throw new \RuntimeException('Could not find extension: ' . $name);
        }
    }

    public function setCWD($dir) {
        $this->executor->executorGlobals->cwd = $dir;
    }

    public function execute($code) {
        try {//编译代码
            $opCodes = $this->executor->compile($code, 'Command line code');
        } catch (Engine\ErrorOccurredException $e) {
            die();
        }//执行的
        return $this->executeOpLines($opCodes);
    }

    public function compile($code){
        try {
            $opCodes = $this->executor->compile($code, 'Command line code');
        } catch (Engine\ErrorOccurredException $e) {
            die();
        }
        DEBUG && var_dump($opCodes);
    }

    public function executeFile($file) {
        //var_dump($file);exit;
        if (empty($file)) {
            throw new \RuntimeException('Filename must not be empty');
        }
        $this->setCWD(dirname($file));
        try {
            loggerInfo(__METHOD__.",".$file);
            $opCodes = $this->executor->compileFile($file);
        } catch (Engine\ErrorOccurredException $e) {
            die();
        }
        //print_r($opCodes);exit;
        return $this->executeOpLines($opCodes);
    }

    public function compileFile($file) {
        if (empty($file)) {
            throw new \RuntimeException('Filename must not be empty');
        }
        $this->setCWD(dirname($file));
        try {
            $opCodes = $this->executor->compileFile($file);
        } catch (Engine\ErrorOccurredException $e) {
            die();
        }
        DEBUG && var_dump($opCodes);
    }

    //执行代码的
    public function executeOpLines(Engine\OpArray $opCodes) {
        try {
            //var_dump($opCodes);exit;
            $retval = $this->executor->execute($opCodes);
            if ($retval) {
                return $retval->getValue();
            }


            $this->executor->shutdown();


            $this->executor->getOutput()->finish();


        } catch (Engine\ErrorOccurredException $e) {
            // Ignore, since the error should be in the OB
        }
        $this->executor->getOutput()->finish(true);
        // Force outputting of any remaining buffers
        return null;
    }

}