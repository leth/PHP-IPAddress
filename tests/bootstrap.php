<?php

function __autoload($class_name) {
	require_once str_replace('_', '/', $class_name . '.php');
}