<?php 

$af = function(){

};

$af();


$af = function(&$c){
	$a = 100;
	echo $a,PHP_EOL;

	$c = 200;
};


$b = 0;
$af($b);
