<?php
use Leth\IPAddress\IPv4;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the IPv4\NetworkAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv4_NetworkAddress_Test extends TestCase
{

	public function providerSubnet()
	{
		$data = array(
			array(32, '255.255.255.255', 1,          '1/256 C'),
			array(31, '255.255.255.254', 2,          '1/128 C'),
			array(30, '255.255.255.252', 4,          '1/64 C'),
			array(29, '255.255.255.248', 8,          '1/32 C'),
			array(28, '255.255.255.240', 16,         '1/16 C'),
			array(27, '255.255.255.224', 32,         '1/8 C'),
			array(26, '255.255.255.192', 64,         '1/4 C'),
			array(25, '255.255.255.128', 128,        '1/2 C'),
			array(24, '255.255.255.000', 256,        '1 C'),
			array(23, '255.255.254.000', 512,        '2 C'),
			array(22, '255.255.252.000', 1024,       '4 C'),
			array(21, '255.255.248.000', 2048,       '8 C'),
			array(20, '255.255.240.000', 4096,       '16 C'),
			array(19, '255.255.224.000', 8192,       '32 C'),
			array(18, '255.255.192.000', 16384,      '64 C'),
			array(17, '255.255.128.000', 32768,      '128 C'),
			array(16, '255.255.000.000', 65536,      '1 B'),
			array(15, '255.254.000.000', 131072,     '2 B'),
			array(14, '255.252.000.000', 262144,     '4 B'),
			array(13, '255.248.000.000', 524288,     '8 B'),
			array(12, '255.240.000.000', 1048576,    '16 B'),
			array(11, '255.224.000.000', 2097152,    '32 B'),
			array(10, '255.192.000.000', 4194304,    '64 B'),
			array( 9, '255.128.000.000', 8388608,    '128 B'),
			array( 8, '255.000.000.000', 16777216,   '1 A'),
			array( 7, '254.000.000.000', 33554432,   '2 A'),
			array( 6, '252.000.000.000', 67108864,   '4 A'),
			array( 5, '248.000.000.000', 134217728,  '8 A'),
			array( 4, '240.000.000.000', 268435456,  '16 A'),
			array( 3, '224.000.000.000', 536870912,  '32 A'),
			array( 2, '192.000.000.000', 1073741824, '64 A'),
			array( 1, '128.000.000.000', 2147483648, '128 A'),
			array( 0, '000.000.000.000', 4294967296, '256 A'),
		);

		// Collapse redundant 0s
		for ($i=0; $i < count($data); $i++) {
			$data[$i][1] = str_replace('00','0', $data[$i][1]);
			$data[$i][1] = str_replace('00','0', $data[$i][1]);
		}

		return $data;
	}

	/**
	 * @dataProvider providerSubnet
	 */
	public function testSubnets($cidr, $subnet, $address_count, $network_class)
	{
		$net = IPv4\NetworkAddress::factory('0.0.0.0', $cidr);

		$this->assertEquals($subnet, (string) $net->get_subnet_mask());
		$this->assertEquals($address_count, $net->get_NetworkAddress_count());
		$this->assertEquals($network_class, $net->get_network_class());
	}

	public function testGlobalNetmask()
	{
		$this->assertEquals('255.255.255.255', (string) IPv4\NetworkAddress::get_global_netmask());
	}

	public function testDodgyBitwiseStuff()
	{
		$block = IPv4\NetworkAddress::factory('10.13.112.20/30');
		$address = IPv4\Address::factory('10.13.112.21');

		$this->assertTrue($block->encloses_address($address));
	}

	public function providerNetworkBroadcastAddress()
	{
		return array(
			array(IPv4\NetworkAddress::factory('192.168.1.1/24'), '192.168.1.0', '192.168.1.255'),
			array(IPv4\NetworkAddress::factory('192.168.0.10/24'), '192.168.0.0', '192.168.0.255'),
		);
	}

	/**
	 * @dataProvider providerNetworkBroadcastAddress
	 */
	public function testNetworkBroadcastAddress($ip, $ex_network, $ex_broadcast)
	{
		$this->assertEquals($ex_network, (string) $ip->get_NetworkAddress());
		$this->assertEquals($ex_broadcast, (string) $ip->get_broadcast_address());
	}

	public function providerSplit()
	{
		$data = array(
			array('192.168.0.0/24', 0, array('192.168.0.0/24')),
			array('192.168.0.0/24', 1, array('192.168.0.0/25', '192.168.0.128/25')),
			array('192.168.0.0/24', 2, array('192.168.0.0/26', '192.168.0.64/26', '192.168.0.128/26', '192.168.0.192/26')),
		);
		foreach ($data as  &$d)
		{
			$d[0] = IPv4\NetworkAddress::factory($d[0]);
			foreach ($d[2] as &$e)
			{
				$e = IPv4\NetworkAddress::factory($e);
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
        $block = IPv4\NetworkAddress::factory('192.168.0.0/32');
		$block->split();
	}

	public function testIteratorInterface()
	{
		$block = IPv4\NetworkAddress::factory('192.168.0.0/30');
		$expected = array('192.168.0.0', '192.168.0.1', '192.168.0.2', '192.168.0.3');
		$actual = array();
		foreach ($block as $key => $ip)
		{
			$actual[] = (string)$ip;
		}
		$this->assertEquals($expected, $actual);
		$this->assertEquals($expected, array_map('strval', $block->toArray()));
	}

	public function testTwoIterators()
	{
		$block = IPv4\NetworkAddress::factory('192.168.0.0/31');
		$expected = array('192.168.0.0', '192.168.0.0', '192.168.0.1', '192.168.0.1', '192.168.0.0', '192.168.0.1');
		$actual = array();
		foreach ($block as $key => $ip)
		{
			$actual[] = (string)$ip;
			foreach ($block as $key2 => $ip2)
			{
				$actual[] = (string)$ip2;
			}
		}
		$this->assertEquals($expected, $actual);
	}

	public function testCountableInterface()
	{
		$block = IPv4\NetworkAddress::factory('192.168.0.0/30');
		$this->assertCount(4, $block);
		$block = IPv4\NetworkAddress::factory('192.168.0.0/24');
		$this->assertEquals(pow(2, 8), count($block));
		$block = IPv4\NetworkAddress::factory('192.168.0.0/16');
		$this->assertEquals(pow(2, 16), count($block));
	}
}
