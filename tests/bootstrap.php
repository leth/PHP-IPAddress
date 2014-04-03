<?php

error_reporting(E_ALL | E_STRICT);

require __DIR__.'/../vendor/autoload.php';

// @todo This shouldn't be needed.
require 'vendor/pear/math_biginteger/BigInteger.php';

$bigint_mode = getenv('MATH_BIGINTEGER_MODE');
if ($bigint_mode !== FALSE)
	define('MATH_BIGINTEGER_MODE', constant('MATH_BIGINTEGER_MODE_'.$bigint_mode));
