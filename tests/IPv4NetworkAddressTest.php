<?php
require_once 'PHPUnit/Framework.php';

/**
 * Tests for the IPv4NetworkAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv4NetworkAddressTest extends PHPUnit_Framework_TestCase
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
		$net = IPv4NetworkAddress::factory('0.0.0.0', $cidr);
		
		$this->assertEquals($subnet, (string) $net->getSubnetMask());
		$this->assertEquals($address_count, $net->getNetworkAddressCount());
		$this->assertEquals($network_class, $net->getNetworkClass());
	}
	
	public function testGlobalNetmask()
	{
		$this->assertEquals('255.255.255.255', (string) IPv4NetworkAddress::getGlobalNetmask());
	}
}
