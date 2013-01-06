<?php

namespace PHPPHP\Engine\OpLines;

class Identical extends \PHPPHP\Engine\OpLine {
    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        $this->result->setValue($this->op1->isIdenticalTo($this->op2));

        $data->nextOp();
    }
}
