<?php

namespace PHPPHP\Engine\OpLines;

use PHPPHP\Engine\FunctionData;

class ClassDef extends \PHPPHP\Engine\OpLine {

    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        $ce = $this->op1;

        //在全局的环境的类数组中添加一个类的信息
        $data->executor->getClassStore()->register($ce);

        $data->nextOp();
    }
}
