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
			array('IPv4_Address', '127.0.0.1',   '127.000.000.001',   '127.0.0.1'),
			array('IPv4_Address', '127.0.0.0',   '127.000.000.000',   '127.0.0.0'),
			array('IPv4_Address', '127.0.0.2',   '127.000.000.002',   '127.0.0.2'),
			array('IPv4_Address', '192.168.1.1', '192.168.001.001', '192.168.1.1'),
			array('IPv4_Address', '192.168.2.1', '192.168.002.001', '192.168.2.1'),
			array('IPv4_Address', '192.168.1.2', '192.168.001.002', '192.168.1.2'),
			array('IPv4_Address', '10.0.0.2',    '010.000.000.002',    '10.0.0.2'),
			array('IPv4_Address', '10.0.0.1',    '010.000.000.001',    '10.0.0.1'),
			array('IPv4_Address', 257,           '000.000.001.001',     '0.0.1.1'),
			
			array('IPv6_Address', new Math_BigInteger(257),                  '0000:0000:0000:0000:0000:0000:0000:0101', '::101'),
			array('IPv6_Address',                                     '::1', '0000:0000:0000:0000:0000:0000:0000:0001', '::1'),
			array('IPv6_Address', '0000:0000:0000:0000:0000:0000:0000:0001', '0000:0000:0000:0000:0000:0000:0000:0001', '::1'),
			array('IPv6_Address',               'fe80::62fb:42ff:feeb:727c', 'fe80:0000:0000:0000:62fb:42ff:feeb:727c', 'fe80::62fb:42ff:feeb:727c'),
		);
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($expected_class, $input, $full, $compact)
	{
		$instances = array(IP_Address::factory($input), $expected_class::factory($input));
		
		foreach ($instances as $instance) {
			$this->assertNotNull($instance);
			$this->assertEquals($full,    $instance->format(IP_Address::FORMAT_FULL));
			$this->assertEquals($compact, $instance->format(IP_Address::FORMAT_COMPACT));
			$this->assertEquals($expected_class, get_class($instance));
		}
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
