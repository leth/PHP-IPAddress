<?php
require_once 'PHPUnit/Framework.php';

/**
 * Tests for the IP_Network_Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6_Network_AddressTest extends PHPUnit_Framework_TestCase
{
	public function testGlobalNetmask()
	{
		$this->assertEquals('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', (string) IPv6_Network_Address::getGlobalNetmask());
	}
}
