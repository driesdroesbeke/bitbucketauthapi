<?php


function pa($mixed, $stop = false) {
	$ar = debug_backtrace(); $key = pathinfo($ar[0]['file']); $key = $key['basename'].':'.$ar[0]['line'];
	$print = array($key => $mixed); echo( '<pre>'.(print_r($print,1)).'</pre>' );
	if($stop == 1) exit();
}



?>