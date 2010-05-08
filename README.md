# PHP-IPAddress

A set of utility classes for working with IP addresses in PHP.
Supports both IPv4 and IPv6 schemes.

### Requirements

*	PHP version 5.3.0 or greater.
*	The [PEAR](http://pear.php.net/) [Math_BigInteger](http://pear.php.net/package/Math_BigInteger/) class to perform arithmetic for some IPv6 address operations.

## Examples

	<?php
	// Creates an instance
	$ip = IPAddress::factory('127.0.0.1');
	
	// Prints "127.0.0.1"
	echo $ip . "\n";
	
	/**
	 * IPAddress::factory(...) will attempt to guess the address version from the arguments 
	 */
	// Returns an instance of IPv4Address
	$ip = IPAddress::factory('127.0.0.1');
	$ip = IPv4Address::factory('127.0.0.1');
	
	// Returns an instance of IPv6Address
	$ip = IPAddress::factory('::1');
	$ip = IPv6Address::factory('::1');
	
	/**
	 * IPNetworkAddress::factory(...) will also attempt to guess protocol versions
	 */
	// Can either be called with the subnet size encoded in the address string,
	$net_addr = IPNetworkAddress::factory('192.168.0.1/24');
	// Or as the second parameter
	$net_addr = IPNetworkAddress::factory('192.168.0.1', 24);
	
	// Prints '192.168.0.0'
	echo $net_addr->getNetworkAddress() . "\n";
	// Prints '192.168.0.255'
	echo $net_addr->getBroadcastAddress() . "\n";
	// Prints '255.255.255.0'
	echo $net_addr->getSubnetMask() . "\n";
		
## Test Cases

To run the test cases, the following commands will do the trick:

*	No-frills tests
	
	phpunit --bootstrap tests/bootstrap.php --include-path src/ tests

*	Generate code coverage reports to 'phpunit/coverage/'

	phpunit --bootstrap tests/bootstrap.php --include-path src/ --coverage-html phpunit/coverage/ tests
	
*	With colours and verbose output

	phpunit --bootstrap tests/bootstrap.php --include-path src/ --colors --verbose tests