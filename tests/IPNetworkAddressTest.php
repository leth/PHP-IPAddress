<?php

class IP_Address_Tester extends IP_Address
{
	public function __construct() {}
	
	public function add($value) {}
	public function subtract($value) {}
		
	public function bitwise_and(IP_Address $other) {}
	public function bitwise_or(IP_Address $other) {}
	public function bitwise_xor(IP_Address $other) {}
	public function bitwise_not() {}
	
	public function format($mode) { return __CLASS__; }
	public function compare_to(IP_Address $other) {}
}

class IP_Network_Address_Tester extends IP_Network_Address
{
	
}

class IPv4_Network_Address_Tester extends IPv4_Network_Address
{
	public static function factory($address, $cidr = NULL)
	{
		$ip = IPv4_Address::factory($address);
		return new IPv4_Network_Address_Tester($ip, $cidr);
	}
	
	public function test_check_ip_version($other)
	{
		return $this->check_ip_version($other->address);
	}
}

class IPv6_Network_Address_Tester extends IPv6_Network_Address
{
	public static function factory($address, $cidr = NULL)
	{
		$ip = IPv6_Address::factory($address);
		return new IPv6_Network_Address_Tester($ip, $cidr);
	}
	public function test_check_ip_version($other)
	{
		return $this->check_ip_version($other->address);
	}
}

/**
 * Tests for the IP_Network_Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IP_Network_Address_Test extends PHPUnit_Framework_TestCase
{
	public function providerFactory()
	{
		return array(
			array('127.0.0.1/16', NULL, '127.0.0.1', 16, '127.0.0.0'),
			array('127.0.0.1', 16, '127.0.0.1', 16, '127.0.0.0'),
			array(IP_Network_Address::factory('127.0.0.1/16'), NULL, '127.0.0.1', 16, '127.0.0.0'),
			array(IP_Network_Address::factory('127.0.0.1/16'), 10, '127.0.0.1', 10, '127.0.0.0'),

			array('::1/16', NULL, '::1', 16, '::0'),
			array('::1', 16, '::1', 16, '::0'),
		);
	}
	
	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($address, $cidr, $expected_address, $expected_cidr, $expected_subnet)
	{
		$ip = IP_Network_Address::factory($address, $cidr);
		
		$this->assertEquals($expected_cidr, $ip->get_cidr());
		$this->assertEquals($expected_address, (string) $ip->get_address());
		$this->assertEquals($expected_subnet, (string) $ip->get_network_start());
	}
	
	
	public function providerFactoryThrowsException()
	{
		return array(
			array(new IP_Address_Tester(), 1),
			array(new IP_Address_Tester(), 3)
		);
	}
	
	/**
	 * @dataProvider providerFactoryThrowsException
	 * @expectedException InvalidArgumentException
	 */
	public function testFactoryThrowsException($address, $cidr)
	{
		IP_Network_Address::factory($address, $cidr);
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
		$ip = IP_Network_Address::factory($address, $cidr);
	}
	
	public function providerUnimplementedException()
	{
		return array(
			array('IP_Network_Address_Tester', 'generate_subnet_mask'),
			array('IP_Network_Address_Tester', 'get_global_netmask'),
		);
	}
	
	/**
	 * @expectedException Exception
	 * @dataProvider providerUnimplementedException
	 */
	public function testUnimplementedException($class, $method)
	{
		$class::$method(NULL);
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
			$data[$i][0] = IP_Network_Address::factory($data[$i][0]);
			$data[$i][1] = IP_Network_Address::factory($data[$i][1]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider providerCompare
	 */
	public function testCompare($left, $right, $expected)
	{
		$cmp = IP_Network_Address::compare($left, $right);
		
		if ($cmp != 0)
			$cmp /= abs($cmp);
		
		$this->assertEquals($expected, $cmp);
	}
	
	public function providerAddressInNetwork()
	{
		return array(
			array(IP_Network_Address::factory('192.168.1.1/24'),  0, NULL, '192.168.1.0'),
			array(IP_Network_Address::factory('192.168.1.1/24'),  1, NULL, '192.168.1.1'),
			array(IP_Network_Address::factory('192.168.1.1/24'),  2, NULL, '192.168.1.2'),
			array(IP_Network_Address::factory('192.168.1.1/24'),  0, FALSE, '192.168.1.255'),
			array(IP_Network_Address::factory('192.168.1.1/24'), -1, NULL, '192.168.1.254'),
			array(IP_Network_Address::factory('192.168.1.1/24'), -2, NULL, '192.168.1.253'),
			array(IP_Network_Address::factory('192.168.1.1/24'), -3, NULL, '192.168.1.252'),

			array(IP_Network_Address::factory('192.168.1.1/24'),  0, NULL, '192.168.1.0'),
			array(IP_Network_Address::factory('192.168.1.1/24'),  1, NULL, '192.168.1.1'),
			array(IP_Network_Address::factory('192.168.1.1/24'),  0, FALSE, '192.168.1.255'),
			array(IP_Network_Address::factory('192.168.1.1/24'), -1, NULL, '192.168.1.254'),
			array(IP_Network_Address::factory('192.168.1.1/24'), -2, NULL, '192.168.1.253'),

			array(IP_Network_Address::factory('10.13.1.254/24'), 0, NULL, '10.13.1.0'),
			array(IP_Network_Address::factory('10.13.1.254/24'), 1, NULL, '10.13.1.1'),
			array(IP_Network_Address::factory('10.13.1.254/24'), 0, FALSE, '10.13.1.255'),
			array(IP_Network_Address::factory('10.13.1.254/24'), -1, NULL, '10.13.1.254'),

			array(IP_Network_Address::factory('10.13.1.254/24'), new Math_BigInteger( 0), NULL, '10.13.1.0'),
			array(IP_Network_Address::factory('10.13.1.254/24'), new Math_BigInteger( 1), NULL, '10.13.1.1'),
			array(IP_Network_Address::factory('10.13.1.254/24'), new Math_BigInteger( 0), FALSE, '10.13.1.255'),
			array(IP_Network_Address::factory('10.13.1.254/24'), new Math_BigInteger(-1), NULL, '10.13.1.254'),
		);
	}
	
	/**
	 * @dataProvider providerAddressInNetwork
	 */
	public function testAddressInNetwork($network, $index, $from_start, $expected)
	{
		$address = $network->get_address_in_network($index, $from_start);
		$this->assertEquals($expected, (string) $address);
	}

	public function providerCheck_ip_version()
	{
		return array(
			array(
				IPv4_Network_Address_Tester::factory('10.1.0.0', 24),
				IPv4_Network_Address_Tester::factory('10.2.0.0', 24),
				IPv6_Network_Address_Tester::factory('::1', 24),
				IPv6_Network_Address_Tester::factory('1::1', 24)
			)
		);
	}
	
	public function providerCheck_ip_version_fail()
	{
		list(list($a4, $b4, $a6, $b6)) = $this->providerCheck_ip_version();
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
	 * @dataProvider providerCheck_ip_version_fail
	 */
	public function test_check_ip_version_fail($left, $right)
	{
		try
		{
			$left->test_check_ip_version($right);
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
	 * @dataProvider providerCheck_ip_version
	 */
	public function test_check_ip_version($a4, $b4, $a6, $b6)
	{
		try
		{
			$a4->test_check_ip_version($b4);
			$b4->test_check_ip_version($a4);

			$a6->test_check_ip_version($b6);
			$b6->test_check_ip_version($a6);
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
			$data[$i][0] = IP_Network_Address::factory($data[$i][0]);
			$data[$i][1] = IP_Network_Address::factory($data[$i][1]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider providerSubnets
	 */
	public function testSubnets($sub1, $sub2, $shares, $encloses)
	{
		$this->assertEquals($shares, $sub1->shares_subnet_space($sub2));
		$this->assertEquals($encloses, $sub1->encloses_subnet($sub2));
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
			$data[$i][0] = IP_Network_Address::factory($data[$i][0]);
			$data[$i][1] = IP_Address::factory($data[$i][1]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider providerEnclosesAddress
	 */
	public function testEnclosesAddress($subnet, $address, $expected)
	{
		$this->assertEquals($expected, $subnet->encloses_address($address));
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
			$data[$i][0] = IP_Network_Address::factory($data[$i][0]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider provideNetworkIdentifiers
	 */
	public function testNetworkIdentifiers($subnet, $expected)
	{
		$this->assertEquals($expected, $subnet->is_network_identifier());
		$this->assertTrue($subnet->get_network_identifier()->is_network_identifier());
	}
	
	public function test__toString()
	{
		$ip = '192.128.1.1/24';
		$this->assertEquals($ip, (string) IP_Network_Address::factory($ip));

		$ip = '::1/24';
		$this->assertEquals($ip, (string) IP_Network_Address::factory($ip));
	}
}
