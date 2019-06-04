<?php 

// $af = function(){

// };

// $af();


$af = function(&$c){
	static $a;

	if(empty($a)){
		$a = 100;
	}
	
	echo $a,PHP_EOL;

	$c = 200;
};


$b = 0;
$af($b);

$af($b);
