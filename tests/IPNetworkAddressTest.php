<?php
require_once 'PHPUnit/Framework.php';

class IPv4NetworkAddressTester extends IPv4NetworkAddress
{
	public static function factory($address, $cidr)
	{
		$ip = IPv4Address::factory($address);
		return new IPv4NetworkAddressTester($ip, $cidr);
	}
	
	public function testCheckIPVersion($other)
	{
		return $this->checkIPVersion($other->address);
	}
}

class IPv6NetworkAddressTester extends IPv6NetworkAddress
{
	public static function factory($address, $cidr)
	{
		$ip = IPv6Address::factory($address);
		return new IPv6NetworkAddressTester($ip, $cidr);
	}
	public function testCheckIPVersion($other)
	{
		return $this->checkIPVersion($other->address);
	}
}

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

	public function providerCheckIPVersion()
	{
		return array(
			array(
				IPv4NetworkAddressTester::factory('10.1.0.0', 24),
				IPv4NetworkAddressTester::factory('10.2.0.0', 24),
				IPv6NetworkAddressTester::factory('::1', 24),
				IPv6NetworkAddressTester::factory('1::1', 24)
			)
		);
	}
	
	public function providerCheckIPVersionFail()
	{
		list(list($a4, $b4, $a6, $b6)) = $this->providerCheckIPVersion();
		return array(
			array($a4, $a6),
			array($a4, $b6),
			array($a6, $a4),
			array($a6, $b4),

			array($b4, $a6),
			array($b4, $b6),
			array($b6, $a4),
			array($b6, $b4),
		);
	}
	
	/**
	 * @dataProvider providerCheckIPVersionFail
	 */
	public function testCheckIPVersionFail($left, $right)
	{
		try
		{
			$left->testCheckIPVersion($right);
			$this->fail('An expected exception was not raised.');
		}
		catch (InvalidArgumentException $e) {
			// We expect this
		}
		catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			// We expect this
		}
		catch (Exception $e) {
			$this->fail('An unexpected exception was raised.' . $e->getMessage());
		}
	}
	
	/**
	 * @dataProvider providerCheckIPVersion
	 */
	public function testCheckIPVersion($a4, $b4, $a6, $b6)
	{
		try
		{
			$a4->testCheckIPVersion($b4);
			$b4->testCheckIPVersion($a4);

			$a6->testCheckIPVersion($b6);
			$b6->testCheckIPVersion($a6);
		}
		catch (Exception $e) {
			$this->fail('An unexpected exception was raised.' . $e->getMessage());
		}
	}
	
	public function providerSubnets()
	{
		$data = array(
			array('2000::/3','2001:630:d0:f104::80a/128', true, true),
			array('2000::/3','2001:630:d0:f104::80a/96', true, true),
			array('2000::/3','2001:630:d0:f104::80a/48', true, true),

			array('2001:630:d0:f104::80a/96', '2000::/3', true, false),
			array('2001:630:d0:f104::80a/48', '2000::/3', true, false),

			array('2000::/3','4000::/3', false, false),
			array('2000::/3','1000::/3', false, false),
		);
		
		for ($i=0; $i < count($data); $i++) { 
			$data[$i][0] = IPNetworkAddress::factory($data[$i][0]);
			$data[$i][1] = IPNetworkAddress::factory($data[$i][1]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider providerSubnets
	 */
	public function testSubnets($sub1, $sub2, $shares, $encloses)
	{
		$this->assertEquals($shares, $sub1->sharesSubnetSpace($sub2));
		$this->assertEquals($encloses, $sub1->enclosesSubnet($sub2));
	}
	
	public function providerEnclosesAddress()
	{
		$data = array(
			array('2000::/3','2001:630:d0:f104::80a', true),
			array('2000::/3','2001:630:d0:f104::80a', true),
			array('2000::/3','2001:630:d0:f104::80a', true),
                                                         
			array('2001:630:d0:f104::80a/96', '2000::', false),
			array('2001:630:d0:f104::80a/48', '2000::', false),

			array('2000::/3','4000::', false),
			array('2000::/3','1000::', false),
		);
		
		for ($i=0; $i < count($data); $i++) { 
			$data[$i][0] = IPNetworkAddress::factory($data[$i][0]);
			$data[$i][1] = IPAddress::factory($data[$i][1]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider providerEnclosesAddress
	 */
	public function testEnclosesAddress($subnet, $address, $expected)
	{
		$this->assertEquals($expected, $subnet->enclosesAddress($address));
	}
	
	public function provideNetworkIdentifiers()
	{
		$data = array(
			array('2000::/3', true),
			array('2000::1/3', false),
			
			array('2000::/3', true),
			array('2000::1/3', false),
		);
		
		for ($i=0; $i < count($data); $i++) { 
			$data[$i][0] = IPNetworkAddress::factory($data[$i][0]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider provideNetworkIdentifiers
	 */
	public function testNetworkIdentifiers($subnet, $expected)
	{
		$this->assertEquals($expected, $subnet->isNetworkIdentifier());
		$this->assertTrue($subnet->getNetworkIdentifier()->isNetworkIdentifier());
	}
}
