<?php
require_once 'PHPUnit/Framework.php';

/**
 * Tests for the IPNetworkAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPNetworkAddressTest extends PHPUnit_Framework_TestCase
{
	public function providerFactory()
	{
		return array(
			array('127.0.0.1/16', NULL, '127.0.0.1', 16, '127.0.0.0'),
			array('127.0.0.1', 16, '127.0.0.1', 16, '127.0.0.0'),

			array('::1/16', NULL, IPv6Address::padV6AddressString('::1'), 16, IPv6Address::padV6AddressString('::0')),
			array('::1', 16, IPv6Address::padV6AddressString('::1'), 16, IPv6Address::padV6AddressString('::0')),
		);
	}
	
	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($address, $cidr, $expected_address, $expected_cidr, $expected_subnet)
	{
		$ip = IPNetworkAddress::factory($address, $cidr);
		
		$this->assertEquals($expected_cidr, $ip->getCIDR());
		$this->assertEquals($expected_address, (string) $ip->getAddress());
		$this->assertEquals($expected_subnet, (string) $ip->getNetworkStart());
	}
	
	public function providerFactoryException()
	{
		return array(
			array('127.0.0.1/16', 16),
			array('127.0.0.1', NULL),
		);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($address, $cidr)
	{
		$ip = IPNetworkAddress::factory($address, $cidr);
	}
	
}
