<?php

namespace PHPPHP\Engine;

class ExecuteData {
    public $executor;
    public $function;
    public $arguments;
    public $opArray = array();
    public $opLine;
    public $parent;
    public $returnValue;
    public $symbolTable = array();
    protected $opPosition = 0;

    public function __construct(
        Executor $executor, 
        OpArray $opArray, 
        FunctionData $function = null) {
        
        $this->executor = $executor;
        $this->opArray = $opArray;//全部的opline
        $this->opLine = $opArray[0];//当前的opline,初始化的时候是第一个
        $this->function = $function;//当前运行的环境的
        $this->returnValue = Zval::ptrFactory();
    }

    //变量的保存的地方
    public function fetchVariable($name) {
        if (!isset($this->symbolTable[$name])) {
            $this->symbolTable[$name] = Zval::ptrFactory();
        }
        return $this->symbolTable[$name];
    }

    public function jump($position) {
        $this->opPosition = $position;
        if (!isset($this->opArray[$position])) {
            $this->opLine = false;
        } else {
            $this->opLine = $this->opArray[$position];
        }
    }

    public function nextOp() {
        $this->jump($this->opPosition + 1);
    }

}