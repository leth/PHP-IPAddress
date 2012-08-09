<?php

error_reporting(E_ALL | E_STRICT);

function autoload($class_name) {
	$file = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
	$file = str_replace('_', DIRECTORY_SEPARATOR, $file).'.php';
	$local_file = 'classes/'.$file;

	if (file_exists($local_file))
		include_once $local_file;
	else
		include_once $file;
}

spl_autoload_register('autoload');
