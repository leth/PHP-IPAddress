<?php
require_once 'PHPUnit/Framework.php';

/**
 * Tests for the IPAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPAddressTest extends PHPUnit_Framework_TestCase
{

	public function providerFactory()
	{
		return array(
			array('127.0.0.1',  '127.0.0.1',   'IPv4Address'),
			array('127.0.0.0',  '127.0.0.0',   'IPv4Address'),
			array('127.0.0.2',  '127.0.0.2',   'IPv4Address'),
			array('192.168.1.1','192.168.1.1', 'IPv4Address'),
			array('192.168.2.1','192.168.2.1', 'IPv4Address'),
			array('192.168.1.2','192.168.1.2', 'IPv4Address'),
			array('10.0.0.2',   '10.0.0.2',    'IPv4Address'),
			array('10.0.0.1',   '10.0.0.1',    'IPv4Address'),
			array('::1', '0000:0000:0000:0000:0000:0000:0000:0001', 'IPv6Address'),
			array('fe80::62fb:42ff:feeb:727c', 'fe80:0000:0000:0000:62fb:42ff:feeb:727c', 'IPv6Address')
		);
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($input, $output, $expected_class)
	{
		$instance = IPAddress::factory($input);
		
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
			array(12345),
			array(-12345),
		);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		IPAddress::factory($input);
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
		$result = IPAddress::compare(IPAddress::factory($a), IPAddress::factory($b));
		
		// Division is to ensure things are either -1, 0 or 1. abs() is to preseve sign.
		$this->assertEquals($expected, $result == 0 ? 0: $result / abs($result));
	}
}
