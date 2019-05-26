<?php

namespace PHPPHP\Engine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\Engine;

//new对象的
class NewOp extends \PHPPHP\Engine\OpLine {

    public $noConstructorJumpOffset;

    protected static $instanceNumber = 0;

    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        self::$instanceNumber++;
        $className = $this->op1->toString();
        $classEntry = $data->executor->getClassStore()->get($className);
        //通过类实例化一个对象
        $instance = $classEntry->instantiate($data, array());
        $instance->setInstanceNumber(self::$instanceNumber);

        //获取构造函数,运行构造函数
        $constructor = $classEntry->getConstructor();
        if ($constructor) {
            $data->executor->executorGlobals->call = new Engine\FunctionCall($data->executor, $constructor, $instance);
        }
        $this->result->setValue(Zval::factory($instance));
        
        if (!$constructor) {
            $data->jump($this->noConstructorJumpOffset);
        } else {
            $data->nextOp();
        }
    }
}
