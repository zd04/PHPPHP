<?php 

/*
var_dump(bb());
require "func1.php";

*/

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

//类的声明不会提前,但是方法的声明会提前的
// $obj1 = new a();

//var_dump($obj1->a2());

//在对象的后面添加类对应的方法
class a{
	public function a3(){
		return 'a->a3';
	}
}

$obj1 = new a();
// $code1 = "
// class a{
// 	public function a3(){
// 		return 'a->a3';
// 	}
// }
// ";
$r = $obj1->a1();

var_dump($r);