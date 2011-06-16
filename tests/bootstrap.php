<?php

define('SYSPATH','');

function autoload($class_name) {
	$file = str_replace('_', '/', $class_name).'.php';
	$local_file = 'classes/'.strtolower($file);
	if (file_exists($local_file))
		include_once $local_file;
	else
		include_once $file;
	
}

spl_autoload_register('autoload');
