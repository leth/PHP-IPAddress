# PHP-IPAddress

A set of utility classes for working with IP addresses in PHP.
Supports both IPv4 and IPv6 schemes.

### Requirements

* PHP version 5.3.0 or greater.
* The [PEAR](http://pear.php.net/) [Math_BigInteger](http://pear.php.net/package/Math_BigInteger/) class

  Required for add & subtract operations on IPv6 addresses, and finding IPs in IPv6 address blocks.

## Examples

```php
<?php
use Leth\IPAddress\IP, Leth\IPAddress\IPv4, Leth\IPAddress\IPv6;
// Creates an instance
$ip = IP\Address::factory('127.0.0.1');

// Prints "127.0.0.1"
echo $ip . "\n";

/**
 * IP\Address::factory(...) will attempt to guess the address version from the arguments
 */
// Returns an instance of IPv4\Address
$ip = IP\Address::factory('127.0.0.1');
$ip = IPv4\Address::factory('127.0.0.1');

// Returns an instance of IPv6\Address
$ip = IP\Address::factory('::1');
$ip = IPv6\Address::factory('::1');

/**
 * IP\NetworkAddress::factory(...) will also attempt to guess protocol versions
 */
// Can either be called with the subnet size encoded in the address string,
$net_addr = IP\NetworkAddress::factory('192.168.0.1/24');
// Or as the second parameter
$net_addr = IP\NetworkAddress::factory('192.168.0.1', 24);

// Prints '192.168.0.0'
echo $net_addr->get_network_address() . "\n";
// Prints '192.168.0.255'
echo $net_addr->get_broadcast_address() . "\n";
// Prints '255.255.255.0'
echo $net_addr->get_subnet_mask() . "\n";

/**
 * For each address of the specified network.
 */
$network = IPv4\NetworkAddress::factory('192.168.0.0/24');
foreach ($network as $ip) {
	// $ip is instance of IPv4\Address with value:
	// 192.168.0.0
	// 192.168.0.1
	// ...
}

$network = IPv4\NetworkAddress::factory('192.168.0.0/24');
// Prints '256'
echo count($network);

/**
 * Merge adjacent NetworkAddress blocks into larger blocks
 */
$small = array(
	IPv4\NetworkAddress::factory('192.168.0.0/24'),
	IPv4\NetworkAddress::factory('192.168.1.0/24')
);
$merged = IP\NetworkAddress::merge($small);
// Prints '1'
echo count($merged);
// Prints '1'
echo $merged[0] == IP\NetworkAddress::factory('192.168.0.0/23');

/**
 * Get specified octet from IP
 */
$ipv4 = IP\Address::factory('192.168.1.102');
// Prints '102'
echo $ipv4->get_octet(-1);
// Prints '168'
echo $ipv4[1];

$ipv6 = IP\Address::factory('2490::fa');
// Prints '250'
echo $ipv6->get_octet(-1);
// Prints '0'
echo $ipv6[5];
```

## Test Cases

To run the test cases, the following commands will do the trick:

*	No-frills tests:

	phpunit -c phpunit.xml.dist

*	Generate code coverage reports into './coverage/':

	phpunit -c phpunit.xml.dist --coverage-html coverage

*	With colours and verbose output:

	phpunit -c phpunit.xml.dist --colors --verbose

*	All together:

	phpunit -c phpunit.xml.dist --coverage-html coverage --colors --verbose
