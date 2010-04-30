<?php
require_once 'PHPUnit/Framework.php';

/**
 * Tests for the IPNetworkAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6NetworkAddressTest extends PHPUnit_Framework_TestCase
{
	public function testGlobalNetmask()
	{
		$this->assertEquals('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', (string) IPv6NetworkAddress::getGlobalNetmask());
	}
}
