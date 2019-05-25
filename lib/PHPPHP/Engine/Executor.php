<?php

namespace PHPPHP\Engine;

class Executor {
    const DO_RETURN = 1;
    const DO_SHUTDOWN = 2;

    const IN_SHUTDOWN = 1;
    const FINISHED_SHUTDOWN = 2;

    public $executorGlobals;

    protected $errorHandler;
    protected $stack;
    protected $current;
    protected $globalScope = array();
    protected $parser;
    protected $shutdown = false;
    protected $compiler;
    protected $files = array();
    protected $output;

    protected $extensions;

    protected $shutdownFunctions = array();
    protected $functionStore;
    protected $constantStore;
    protected $classStore;

    protected $preExecute;
    protected $postExecute;

    public function __construct(
        FunctionStore $functionStore, 
        ConstantStore $constantStore, 
        ClassStore $classStore) {

        $this->executorGlobals = new ExecutorGlobals;//全局的数据容器
        $this->parser = new Parser;//解析器
        $this->compiler = new Compiler($functionStore);//编译器

        $this->functionStore = $functionStore;//环境的方法列表
        $this->constantStore = $constantStore;//环境的常量列表
        $this->classStore = $classStore;//环境的class列表

        //扩展
        $this->extensions = new \SplObjectStorage;
        //栈
        $this->stack = new \SplStack;
        //错误
        $this->errorHandler = new ErrorHandler\Internal;
    }

    public function shutdown() {
        if ($this->shutdown == self::IN_SHUTDOWN) {
            $this->shutdown = self::FINISHED_SHUTDOWN;
        } elseif (!$this->shutdown) {
            $this->shutdown = self::IN_SHUTDOWN;
            $k = 0;
            while ($this->shutdown == self::IN_SHUTDOWN && isset($this->shutdownFunctions[$k])) {
                $cb = $this->shutdownFunctions[$k];
                $cb($this, array(), Zval::ptrFactory());
                $k++;
            }
        }
    }

    public function getErrorHandler() {
        return $this->errorHandler;
    }

    public function setErrorHandler(ErrorHandler $handler) {
        $this->errorHandler = $handler;
    }

    public function raiseError($level, $message, $extra = '', $adFunc = true) {
        $file = $this->current->opArray->getFileName();
        $line = $this->current->opLine->lineno;
        $this->errorHandler->handle($this, $level, $message, $file, $line, $extra, $adFunc);
    }

    public function getStack() {
        return $this->stack;
    }

    public function getOutput() {
        return $this->output;
    }

    public function setOutput(Output $output) {
        $this->output = $output;
    }

    public function hasFile($fileName) {
        return isset($this->files[$fileName]);
    }

    public function compileFile($fileName) {
        $this->compiler->setFileName($fileName, dirname($fileName));

        if (!isset($this->files[$fileName])) {
            $code = file_get_contents($fileName);//从文件获取源码
            /*获取到的是编译的节点parse_node，就是AST*/
            $this->files[$fileName] = $this->parseCode($code, $fileName);//代码
            //echo __METHOD__,PHP_EOL;
            //var_dump($fileName,$this->files[$fileName]);//exit;
        }
        return $this->compileCode($this->files[$fileName], $fileName);
    }

    public function compile($code, $context) {
        //解析代码的
        $ast = $this->parseCode($code, $context);//代码

        DEBUG && var_dump($ast);
        $this->compiler->setFileName($context, $this->executorGlobals->cwd);

        return $this->compileCode($ast, $context);
    }

    protected function compileCode(array $ast, $file) {
        try {
            //var_dump($ast);exit;
            /*通过AST再编译的*/
            return $this->compiler->compile($ast);

        } catch (CompileException $e) {
            $line = $e->getRawLine();
            $this->errorHandler->handle($this, E_COMPILE_ERROR, $e->getMessage(), $file, $line);
            throw new ErrorOccurredException($message, E_COMPILE_ERROR);
        }
    }

    protected function parseCode($code, $file) {
        try {
            //编译代码的到AST
            return $this->parser->parse($code);

        } catch (\PHPParser_Error $e) {
            $message = 'syntax error, ' . str_replace('Unexpected', 'unexpected', $e->getMessage());
            $line = $e->getRawLine();
            $this->errorHandler->handle($this, E_PARSE, $message, $file, $line);
            throw new ErrorOccurredException($message, E_PARSE);
        }
    }

    //带环境的运行opcode的
    public function execute(
        OpArray $opArray, 
        array &$symbolTable = array(), 
        FunctionData $function = null, 
        array $args = array(), 
        Zval $result = null, 
        Objects\ClassInstance $ci = null) {

        $shutdownScope = $this->shutdown;
        if ($this->shutdown == self::FINISHED_SHUTDOWN) return;

        $opArray->registerExecutor($this);//保存当前的运行器

        //echo __METHOD__,PHP_EOL;
        //var_dump($this);

        $scope = new ExecuteData($this, $opArray, $function);/*运行环境的的变量信息*/
        $scope->arguments = $args;
        $scope->ci = $ci;
        $preExecute = $this->preExecute;
        $postExecute = $this->postExecute;

        //这个就是上一层的是那一个
        if ($this->current) {
            $scope->parent = $this->current;
        }

        $this->current = $scope;

        if ($symbolTable || $function) {
            $scope->symbolTable =& $symbolTable;
        } else {
            $scope->symbolTable =& $this->executorGlobals->symbolTable;
        }

        //返回值的信息，如果没有返回值的话就是生成一个默认的返回值
        $scope->returnValue = $result ?: Zval::ptrFactory();
        if ($function && $function->isByRef()) {
            $scope->returnValue->makeRef();
        }

        /*
        $this->shutdown == $shutdownScope 这个就是说
         */
        while ($this->shutdown == $shutdownScope && $scope->opLine) {

            //运行之前的回调
            if ($preExecute) {
                call_user_func($preExecute, $scope);
            }

            $ret = $scope->opLine->execute($scope);

            //运行之后的回调
            if ($postExecute) {
                call_user_func($postExecute, $scope, $ret);
            }

            if ($this->shutdown == $shutdownScope 
                && $this->executorGlobals->timeLimit 
                && $this->executorGlobals->timeLimitEnd < time()) {

                $limit = $this->executorGlobals->timeLimit;
                $message = sprintf('Maximum execution time of %d second%s exceeded', $limit, $limit == 1 ? '' : 's');
                $this->errorHandler->handle($this, E_ERROR, $message, $opArray->getFileName(), $scope->opLine->lineno, '', false);
                return;
            }
            switch ($ret) {
                case self::DO_RETURN:
                    $this->current = $this->current->parent;
                    return;
                case self::DO_SHUTDOWN:
                    $this->shutdown();
                    return;
            }
        }
        if ($this->shutdown) {
            return;
        }
        die('Should never reach this point!');
    }

    public function getCallback($callback) {
        if ($callback instanceof Zval) {
            $callback = $callback->getValue();
        }
        if (is_string($callback)) {
            $cb = $this->functionStore->get($callback);
            return function(Executor $executor, array $args = array(), Zval $return = null) use ($cb) {
                return $cb->execute($executor, $args, $return);
            };
        } elseif (is_array($callback)) {
            $class = $callback[0];
            $method = $callback[1];
            if ($class instanceof Zval) {
                $class = $class->getValue();
            }
            if ($method instanceof Zval) {
                $method = $method->getValue();
            }
            if ($class instanceof Objects\ClassInstance) {
                return function(Executor $executor, array $args = array(), Zval $return = null) use ($class, $method) {
                    $class->callMethod($executor->getCurrent(), (string) $method, $args, $return);
                };
            }
            if (is_string($class)) {
                $class = $this->classStore->get($class);
            }
            if ($class instanceof Objects\ClassEntry) {
                // Static method!
                return function(Executor $executor, array $args = array(), Zval $return = null) use ($class, $method) {
                    $class->callMethod($executor->getCurrent(), null, (string) $method, $args, $return);
                };
            }
        } elseif ($callback instanceof FunctionData) {
        	return function(Executor $executor, array $args = array(), Zval $return = null) use ($callback) {
        		return $callback->execute($executor, $args, $return);
        	};
        }
        throw new \RuntimeException('Invalid Callback Specified');
    }

    public function getCurrent() {
        return $this->current;
    }

    public function getFunctionStore() {
        return $this->functionStore;
    }

    public function getConstantStore() {
        return $this->constantStore;
    }

    public function getClassStore() {
        return $this->classStore;
    }

    public function getExtensions() {
        return $this->extensions;
    }

    public function registerShutdownFunction($cb) {
        $this->shutdownFunctions[] = $cb;
    }

    //
    public function registerExtension(Extension $extension) {
        if (!$this->extensions->contains($extension)) {
            $extension->register($this);
            $this->extensions->attach($extension);
            if (method_exists($extension, 'preExecute')) {
                if ($this->preExecute) {
                    $old = $this->preExecute;
                    $this->preExecute = function($scope) use ($old, $extension) {
                        $extension->preExecute($scope);
                        call_user_func($old, $scope);
                    };
                } else {
                    $this->preExecute = array($extension, 'preExecute');
                }
            }
            if (method_exists($extension, 'postExecute')) {
                if ($this->postExecute) {
                    $old = $this->postExecute;
                    $this->postExecute = function($scope) use ($old, $extension) {
                        $extension->postExecute($scope);
                        call_user_func($old, $scope);
                    };
                } else {
                    $this->postExecute = array($extension, 'postExecute');
                }
            }
        }
    }
}
