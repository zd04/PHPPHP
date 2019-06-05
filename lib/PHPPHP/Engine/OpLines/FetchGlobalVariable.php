<?php

namespace PHPPHP\Engine\OpLines;

use PHPPHP\Engine\Zval;

class FetchGlobalVariable extends \PHPPHP\Engine\OpLine {

    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        $varName = $this->op1->toString();
        if (!isset($data->executor->executorGlobals->symbolTable[$varName])) {
            $data->executor->executorGlobals->symbolTable[$varName] = Zval::ptrFactory();
        }
        /*从全局的变量符号表拷贝到当前运行栈的作用域符号表的*/
        $data->symbolTable[$varName] = $data->executor->executorGlobals->symbolTable[$varName];
        $data->nextOp();
    }

}