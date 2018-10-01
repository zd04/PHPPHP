<?php

namespace PHPPHP\Engine;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_NodeTraverser;
use PHPParser_NodeVisitor_NameResolver;

class Parser {

    protected $parser;
    protected $traverser;
    
    public function __construct() {
        $this->parser = new PHPParser_Parser(new PHPParser_Lexer);
        $this->traverser = new PHPParser_NodeTraverser;
        $this->traverser->addVisitor(new PHPParser_NodeVisitor_NameResolver);
    }
    
    public function parse($code) {
        //获取ast的
        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);//这个地方为什么还要在遍历ast??
        return $ast;
    }

}