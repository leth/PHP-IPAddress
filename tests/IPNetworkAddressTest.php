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
	
	
	public function providerCompare()
	{
		$data = array(
			array('0.0.0.0/16', '0.0.0.0/16', 0),
			array('0.0.0.0/16', '0.0.0.1/16', -1),
			array('0.0.0.1/16', '0.0.0.0/16', 1),
			array('127.0.0.1/16' , '127.0.0.1/16', 0),
			array('127.0.10.1/16', '127.0.2.1/16', 1),
			array('127.0.2.1/16' , '127.0.10.1/16', -1),
			// TODO add more addresses and v6 addresses
		);
		for ($i=0; $i < count($data); $i++) { 
			$data[$i][0] = IPNetworkAddress::factory($data[$i][0]);
			$data[$i][1] = IPNetworkAddress::factory($data[$i][1]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider providerCompare
	 */
	public function testCompare($left, $right, $expected)
	{
		$cmp = IPNetworkAddress::compare($left, $right);
		
		if ($cmp != 0)
			$cmp /= abs($cmp);
		
		$this->assertEquals($expected, $cmp);
	}
	
	public function providerAddressInNetwork()
	{
		return array(
			array(IPNetworkAddress::factory('192.168.1.1/24'),  0, NULL, '192.168.1.0'),
			array(IPNetworkAddress::factory('192.168.1.1/24'),  1, NULL, '192.168.1.1'),
			array(IPNetworkAddress::factory('192.168.1.1/24'),  2, NULL, '192.168.1.2'),
			array(IPNetworkAddress::factory('192.168.1.1/24'),  0, FALSE, '192.168.1.255'),
			array(IPNetworkAddress::factory('192.168.1.1/24'), -1, NULL, '192.168.1.254'),
			array(IPNetworkAddress::factory('192.168.1.1/24'), -2, NULL, '192.168.1.253'),
			array(IPNetworkAddress::factory('192.168.1.1/24'), -3, NULL, '192.168.1.252'),

			array(IPNetworkAddress::factory('192.168.1.1/24'), new Math_BigInteger( 0), NULL, '192.168.1.0'),
			array(IPNetworkAddress::factory('192.168.1.1/24'), new Math_BigInteger( 1), NULL, '192.168.1.1'),
			array(IPNetworkAddress::factory('192.168.1.1/24'), new Math_BigInteger( 0), FALSE, '192.168.1.255'),
			array(IPNetworkAddress::factory('192.168.1.1/24'), new Math_BigInteger(-1), NULL, '192.168.1.254'),
			array(IPNetworkAddress::factory('192.168.1.1/24'), new Math_BigInteger(-2), NULL, '192.168.1.253'),
		);
	}
	
	/**
	 * @dataProvider providerAddressInNetwork
	 */
	public function testAddressInNetwork($network, $index, $from_start, $expected)
	{
		$address = $network->getAddressInNetwork($index, $from_start);
		$this->assertEquals($expected, (string) $address);
	}
}
