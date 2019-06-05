<?php

namespace PHPPHP\Engine\FunctionData;

use PHPPHP\Engine;

class User extends Base {
    protected $opArray;
    protected $byRef = false;
    protected $params = array();

    public function __construct(Engine\OpArray $opArray, $byRef = false, array $params = array()) {
        var_dump($opArray);
        $this->opArray = $opArray;/*函数体*/
        $this->byRef = $byRef;/*返回值类型*/
        $this->params = $params;/*参数的*/
    }

    protected function getFileName() {
        return $this->opArray->getFileName();
    }


    public function execute(Engine\Executor $executor, array $args, Engine\Zval\Ptr $return, \PHPPHP\Engine\Objects\ClassInstance $ci = null, \PHPPHP\Engine\Objects\ClassEntry $ce = null) {
        $scope = array();
        if (!$args) {
            $args = array();
        }
        $this->checkParams($executor, $args);
        if ($this->byRef) {
            $return->makeRef();
        }
//function execute(OpArray $opArray, array &$symbolTable = array(), FunctionData $function = null, array $args = array(), Zval $result = null, Objects\ClassInstance $ci = null) 
        $executor->execute($this->opArray, $scope, $this, $args, $return, $ci, $ce);
    }
}
