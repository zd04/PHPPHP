<?php 

function aa()
{
	static $bb;

	echo $bb,PHP_EOL;
	
	if(empty($bb)){
		$bb = 100;
	}

	
}

aa();
aa();