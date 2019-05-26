<?php

namespace PHPPHP\Engine\OpLines;

use PHPPHP\Engine;

class InitFCallByName extends \PHPPHP\Engine\OpLine {

    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        $ci = $this->op1;
        $funcName = $this->op2->toString();
        if ($ci) {
            if ($ci->isObject()) {
                //var_dump($ci->getValue()->getClassEntry()->getMethodStore());exit;

                $ci = $ci->getValue();
                $functionData = $ci->getClassEntry()
                ->getMethodStore()
                ->get($funcName);


            } else {
                throw new \RuntimeException(sprintf('Call to a member function %s() on a non-object', $funcName));
            }
        } else {
            $functionData = $data->executor->getFunctionStore()->get($funcName);
        }
        
        //执行环境,第一个参数就是当前的环境,第二个是当前的方法信息,第三个是当前的实例
        $data->executor->executorGlobals->call = new Engine\FunctionCall($data->executor, $functionData, $ci);
        
        $data->nextOp();
    }

}