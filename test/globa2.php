<?php 

$cc = 0;
function aa()
{
	$cc = 100;
	var_dump($cc);

	global $cc;
	//$cc = 200;
	var_dump($cc);
}

aa();