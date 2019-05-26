<?php

namespace PHPPHP\Engine;

class FunctionStore {
    /** @var FunctionData[] */
    protected $functions = array();

    public function alias($newName, $existingName) {
        $this->register($newName, $this->get($existingName));
    }

    public function register($name, FunctionData $func) {
        $name = strtolower($name);
        if (isset($this->functions[$name])) {
            throw new \RuntimeException("Function $name already defined");
        }
        $func->setName($name);
        $this->functions[$name] = $func;

        //var_dump("name:".$name,"FunctionData");
    }

    public function exists($name) {       
        return isset($this->functions[strtolower($name)]);
    }

    public function get($name) {
        $name = strtolower($name);
        if (!isset($this->functions[$name])) {
            //var_dump("function::exists",$this->functions);exit;
            throw new \RuntimeException(sprintf('Call to undefined function %s', $name));
        }

        return $this->functions[$name];
    }
    
    public function getName(FunctionData $func) {
        foreach ($this->functions as $name => $test) {
            if ($test === $func) {
                return $name;
            }
        }
        return '';
    }

    //public function getFunctions(){
    //    return $this->functions;
    //}
}