<?php

/**
 * Tests for the IP_Network_Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6_Network_Address_Test_Core extends PHPUnit_Framework_TestCase
{
	public function test_global_netmask()
	{
		$this->assertEquals('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', (string) IPv6_Network_Address::getGlobalNetmask());
	}
}
