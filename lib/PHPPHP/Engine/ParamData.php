<?php

namespace PHPPHP\Engine;

class ParamData {

    public $name;//参数名称
    public $isOptional = false;//是否是可选参数
    public $isRef = false;//是否是引用类型的
    public $type = null;
    public $lineno = -1;


    public function __construct($name, $isRef = false, $type = null, $isOptional = false, $lineno = -1) {
        $this->name = $name;
        $this->isRef = $isRef;
        $this->type = $type;
        $this->isOptional = $isOptional;
        $this->lineno = $lineno;
    }
}