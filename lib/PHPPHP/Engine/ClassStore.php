<?php

namespace PHPPHP\Engine;

use PHPPHP\Engine\Objects\ClassEntry;

class ClassStore {
    /** @var ClassEntry[] */
    protected $classes = array();

    public function register(ClassEntry $ce) {
        $lcname = strtolower($ce->getName());
        //var_dump("classregister",$lcname);
        if (isset($this->classes[$lcname])) {
            echo sprintf("[WARN]: Class %s already defined", $ce->getName()),PHP_EOL;

            //$this->classes[$lcname] = $this->classMerge($this->classes[$lcname],$ce);
            return;
            throw new \RuntimeException(sprintf("Class %s already defined", $ce->getName()));
        }
        $this->classes[$lcname] = $ce;
    }

    /*merge*/
    //public function classMerge(ClassEntry $old, ClassEntry $ce){
    //    var_dump("classMerge",$old->merge($ce));
    //    return $old->merge($ce);
    //}

    public function exists($name) {
        return isset($this->classes[strtolower($name)]);
    }

    public function get($name) {
        $name = strtolower($name);
        if (!isset($this->classes[$name])) {
            throw new \RuntimeException(sprintf('Undefined class %s', $name));
        }

        return $this->classes[$name];
    }

    public function getNames() {
        return array_keys($this->classes);
    }
}
