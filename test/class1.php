<?php 

class a{
	public function a2(){
		return "a->a2";
	}
}


class a{
	public function a1(){
		return "a->a1";
	}
}

$obj1 = new a();

var_dump($obj1->a2());