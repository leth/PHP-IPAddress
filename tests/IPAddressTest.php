<?php

/**
 * Tests for the IP_Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IP_Address_Test extends PHPUnit_Framework_TestCase
{

	public function providerFactory()
	{
		return array(
			array(IPv4_Address::factory('127.0.0.1'),  '127.0.0.1',   'IPv4_Address'),
			array('127.0.0.1',  '127.0.0.1',   'IPv4_Address'),
			array('127.0.0.0',  '127.0.0.0',   'IPv4_Address'),
			array('127.0.0.2',  '127.0.0.2',   'IPv4_Address'),
			array('192.168.1.1','192.168.1.1', 'IPv4_Address'),
			array('192.168.2.1','192.168.2.1', 'IPv4_Address'),
			array('192.168.1.2','192.168.1.2', 'IPv4_Address'),
			array('10.0.0.2',   '10.0.0.2',    'IPv4_Address'),
			array('10.0.0.1',   '10.0.0.1',    'IPv4_Address'),
			array(257,          '0.0.1.1',     'IPv4_Address'),
			array(new Math_BigInteger(257), '0000:0000:0000:0000:0000:0000:0000:0101', 'IPv6_Address'),
			array('::1', '0000:0000:0000:0000:0000:0000:0000:0001', 'IPv6_Address'),
			array('fe80::62fb:42ff:feeb:727c', 'fe80:0000:0000:0000:62fb:42ff:feeb:727c', 'IPv6_Address')
		);
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($input, $output, $expected_class)
	{
		$instance = IP_Address::factory($input);
		
		$this->assertNotNull($instance);
		$this->assertEquals($output, (string) $instance);
		$this->assertEquals($expected_class, get_class($instance));
	}
	
	public function providerFactoryException()
	{
		return array(
			array('cake'),
			array('12345'),
			array('-12345'),
			array(-12345),
		);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		IP_Address::factory($input);
	}
	
	public function providerCompare()
	{
		return array(
			array('127.0.0.1', '127.0.0.1', 0),
			array('127.0.0.0', '127.0.0.1', -1),
			array('127.0.0.0', '127.0.0.2', -1),
			array('127.0.0.1', '127.0.0.2', -1),
			array('127.0.0.2', '127.0.0.1', 1),
			array('10.0.0.1',  '127.0.0.2', -1)
		);
	}
	
	/**
	 * @dataProvider providerCompare
	 */
	public function testCompare($a, $b, $expected)
	{
		$result = IP_Address::compare(IP_Address::factory($a), IP_Address::factory($b));
		
		// Division is to ensure things are either -1, 0 or 1. abs() is to preseve sign.
		$this->assertEquals($expected, $result == 0 ? 0: $result / abs($result));
	}
}
