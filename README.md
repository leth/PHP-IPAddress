# PHP-IPAddress

A set of utility classes for working with IP addresses in PHP.
Supports both IPv4 and IPv6 schemes.

### Requirements

*	PHP version 5.3.0 or greater.
*	(optional) The [PEAR](http://pear.php.net/) [Math_BigInteger](http://pear.php.net/package/Math_BigInteger/) class.
    *		Required for add & subtract operations on IPv6 addresses, and finding IPs in IPv6 address blocks.

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
// Returns an instance of IPv4Address
$ip = IP\Address::factory('127.0.0.1');
$ip = IPv4\Address::factory('127.0.0.1');

// Returns an instance of IPv6Address
$ip = IP\Address::factory('::1');
$ip = IPv6\Address::factory('::1');

/**
 * IP_Network_Address::factory(...) will also attempt to guess protocol versions
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
