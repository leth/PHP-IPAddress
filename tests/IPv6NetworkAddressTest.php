<?php

/**
 * Tests for the IP_Network_Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6_Network_Address_Test extends PHPUnit_Framework_TestCase
{
	public function test_global_netmask()
	{
		$this->assertEquals('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', (string) IPv6_Network_Address::get_global_netmask());
	}

	public function providerSplit()
	{
		$data = array(
			array('::0/126', 0, array('::0/126')),
			array('::0/126', 1, array('::0/127', '::2/127')),
			array('::0/126', 2, array('::0/128', '::1/128', '::2/128', '::3/128')),
		);
		foreach ($data as  &$d)
		{
			$d[0] = IPv6_Network_Address::factory($d[0]);
			foreach ($d[2] as &$e)
			{
				$e = IPv6_Network_Address::factory($e);
			}
		}
		return $data;
	}

	/**
	 * @dataProvider providerSplit
	 */
	public function testSplit($block, $degree, $expected)
	{
		$this->assertEquals($expected, $block->split($degree));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSplitBeyondRange()
	{
		$block = IPv6_Network_Address::factory('::0/128');
		$block->split();
	}
}
