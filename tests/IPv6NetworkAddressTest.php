<?php
use Leth\IPAddress\IPv6;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the IP\NetworkAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6_NetworkAddress_Test extends TestCase
{
	public function test_global_netmask()
	{
		$this->assertEquals('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', (string) IPv6\NetworkAddress::get_global_netmask());
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
			$d[0] = IPv6\NetworkAddress::factory($d[0]);
			foreach ($d[2] as &$e)
			{
				$e = IPv6\NetworkAddress::factory($e);
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

    public function testSplitBeyondRange()
	{
        $this->expectException(InvalidArgumentException::class);
        $block = IPv6\NetworkAddress::factory('::0/128');
		$block->split();
	}

	public function testIterationInterface()
	{
		$block = IPv6\NetworkAddress::factory('::0/126');
		$expected = array('::0', '::1', '::2', '::3');
		$actual = array();
		foreach ($block as $key => $ip)
		{
			$actual[] = (string)$ip;
		}
		$this->assertEquals($expected, $actual);
	}

	public function testCountableInterface()
	{
		$block = IPv6\NetworkAddress::factory('::0/126');
		$this->assertEquals(4, count($block));
		$block = IPv6\NetworkAddress::factory('::0/120');
		$this->assertEquals(pow(2, 8), count($block));
	}
}
