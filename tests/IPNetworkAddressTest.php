<?php
use Leth\IPAddress\IP, Leth\IPAddress\IPv4, Leth\IPAddress\IPv6;
use Leth\IPAddress\IP\Address;
use Leth\IPAddress\IP\NetworkAddress;
use PHPUnit\Framework\TestCase;

class IP_Address_Tester extends IP\Address
{
	public function __construct() {}

	public function add($value): IP\Address {return Address::factory('0.0.0.0');}
	public function subtract($value): IP\Address {return Address::factory('0.0.0.0');}

	public function bitwise_and(IP\Address $other) {return  $other;}
	public function bitwise_or(IP\Address $other): Address {return  $other;}
	public function bitwise_xor(IP\Address $other): Address {return  $other;}
	public function bitwise_not(): Address {return Address::factory('0.0.0.0');}

	public function format(int $mode): string { return __CLASS__; }
	public function compare_to(IP\Address $other): int {return  0;}
}

class IP_NetworkAddress_Tester extends IP\NetworkAddress
{
	public function split(int $times = 1): array {return  [];}
}

class IPv4_NetworkAddress_Tester extends IPv4\NetworkAddress
{
	public static function factory(NetworkAddress|Address|string $address, int|string|null $cidr = NULL): IPv4_NetworkAddress_Tester
    {
		$ip = IPv4\Address::factory($address);
		return new IPv4_NetworkAddress_Tester($ip, $cidr);
	}

	public function test_check_IP_version(NetworkAddress $other): void
    {
		$this->check_IP_version($other->address);
	}
}

class IPv6_NetworkAddress_Tester extends IPv6\NetworkAddress
{
	public static function factory(NetworkAddress|Address|string $address, int|string|null $cidr = NULL): IPv6_NetworkAddress_Tester
    {
		$ip = IPv6\Address::factory($address);
		return new IPv6_NetworkAddress_Tester($ip, $cidr);
	}
	public function test_check_IP_version(NetworkAddress $other): void
	{
		$this->check_IP_version($other->address);
	}
}

/**
 * Tests for the IP\NetworkAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IP_NetworkAddress_Test extends TestCase
{
	public function providerFactory(): array
    {
		return array(
			array('127.0.0.1/16', NULL, '127.0.0.1', 16, '127.0.0.0'),
			array('127.0.0.1', 16, '127.0.0.1', 16, '127.0.0.0'),
			array('127.0.0.1/32', NULL, '127.0.0.1', 32, '127.0.0.1'),
			array('127.0.0.1', 32, '127.0.0.1', 32, '127.0.0.1'),
			array(IP\NetworkAddress::factory('127.0.0.1/16'), NULL, '127.0.0.1', 16, '127.0.0.0'),
			array(IP\NetworkAddress::factory('127.0.0.1/16'), 10, '127.0.0.1', 10, '127.0.0.0'),

			array('::1/16', NULL, '::1', 16, '::0'),
			array('::1', 16, '::1', 16, '::0'),
			array('::1/128', NULL, '::1', 128, '::1'),
			array('::1', 128, '::1', 128, '::1'),

		);
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory(string|IP\NetworkAddress $address, string|int|null $cidr, string $expected_address, int $expected_cidr, string $expected_subnet): void
    {
		$ip = IP\NetworkAddress::factory($address, $cidr);

		$this->assertEquals($expected_cidr, $ip->get_cidr());
		$this->assertEquals($expected_address, (string) $ip->get_address());
		$this->assertEquals($expected_subnet, (string) $ip->get_network_start());
	}


	public function providerFactoryThrowsException(): array
    {
		return array(
			array(new IP_Address_Tester(), 1),
			array(new IP_Address_Tester(), 3)
		);
	}

	/**
	 * @dataProvider providerFactoryThrowsException
	 *
     */
	public function testFactoryThrowsException(IP_Address_Tester $address, int $cidr): void
    {
        $this->expectException(\InvalidArgumentException::class);
        IP\NetworkAddress::factory($address, $cidr);
	}

	public function provideFactoryParseCIDR(): array
    {
		return array(
			array('127.0.0.1/16', 24, 24),
			array('127.0.0.1', NULL, 32),
			array('127.0.0.1/24', NULL, 24),
			array('::1', NULL, 128),
			array('::1/58', 64, 64),
			array('::1/58', NULL, 58),
		);
	}

	/**
	 * @dataProvider provideFactoryParseCIDR
	 */
	public function testParseCIDR(string $address, string|int|null $cidr, int $expected): void
    {
		$network = IP\NetworkAddress::factory($address, $cidr);
		$this->assertEquals($expected, $network->get_cidr());
	}

	public function providerUnimplementedException(): array
    {
		return array(
			#array('IP_NetworkAddress_Tester', 'generate_subnet_mask'),
			array('IP_NetworkAddress_Tester', 'get_global_netmask'),
		);
	}

	/**
	 *
     * @dataProvider providerUnimplementedException
	 */
	public function testUnimplementedException(string $class, string $method): void
    {
        $this->expectException(\LogicException::class);
        $class::$method(NULL);
	}

	public function providerCompare(): array
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
		foreach ($data as &$d)
		{
			$d[0] = IP\NetworkAddress::factory($d[0]);
			$d[1] = IP\NetworkAddress::factory($d[1]);
		}

		return $data;
	}

	/**
	 * @dataProvider providerCompare
	 */
	public function testCompare(IP\NetworkAddress $left, IP\NetworkAddress $right, int $expected): void
	{
		$cmp = IP\NetworkAddress::compare($left, $right);

		if ($cmp !== 0) {
            $cmp /= abs($cmp);
        }

		$this->assertEquals($expected, $cmp);
	}

	public function providerAddressInNetwork(): array
    {
		return array(
			array(IP\NetworkAddress::factory('192.168.1.1/24'),  0, NULL, '192.168.1.0'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'),  1, NULL, '192.168.1.1'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'),  2, NULL, '192.168.1.2'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'),  0, FALSE, '192.168.1.255'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'), -1, NULL, '192.168.1.254'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'), -2, NULL, '192.168.1.253'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'), -3, NULL, '192.168.1.252'),

			array(IP\NetworkAddress::factory('192.168.1.1/24'),  0, NULL, '192.168.1.0'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'),  1, NULL, '192.168.1.1'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'),  0, FALSE, '192.168.1.255'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'), -1, NULL, '192.168.1.254'),
			array(IP\NetworkAddress::factory('192.168.1.1/24'), -2, NULL, '192.168.1.253'),

			array(IP\NetworkAddress::factory('10.13.1.254/24'), 0, NULL, '10.13.1.0'),
			array(IP\NetworkAddress::factory('10.13.1.254/24'), 1, NULL, '10.13.1.1'),
			array(IP\NetworkAddress::factory('10.13.1.254/24'), 0, FALSE, '10.13.1.255'),
			array(IP\NetworkAddress::factory('10.13.1.254/24'), -1, NULL, '10.13.1.254'),

			array(IP\NetworkAddress::factory('10.13.1.254/24'), new \Math_BigInteger( 0), NULL, '10.13.1.0'),
			array(IP\NetworkAddress::factory('10.13.1.254/24'), new \Math_BigInteger( 1), NULL, '10.13.1.1'),
			array(IP\NetworkAddress::factory('10.13.1.254/24'), new \Math_BigInteger( 0), FALSE, '10.13.1.255'),
			array(IP\NetworkAddress::factory('10.13.1.254/24'), new \Math_BigInteger(-1), NULL, '10.13.1.254'),
		);
	}

	/**
	 * @dataProvider providerAddressInNetwork
	 */
	public function testAddressInNetwork(IP\NetworkAddress $network, int|Math_BigInteger $index,  ?bool $from_start, string $expected): void
    {
		$address = $network->get_address_in_network($index, $from_start);
		$this->assertEquals($expected, (string) $address);
	}

	public function providerCheck_IP_version(): array
    {
		return array(
			array(
				IPv4_NetworkAddress_Tester::factory('10.1.0.0', 24),
				IPv4_NetworkAddress_Tester::factory('10.2.0.0', 24),
				IPv6_NetworkAddress_Tester::factory('::1', 24),
				IPv6_NetworkAddress_Tester::factory('1::1', 24)
			)
		);
	}

	public function providerCheck_IP_version_fail(): array
    {
		[[$a4, $b4, $a6, $b6]] = $this->providerCheck_IP_version();
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
	 * @dataProvider providerCheck_IP_version_fail
	 */
	public function test_check_IP_version_fail(IPv4_NetworkAddress_Tester|IPv6_NetworkAddress_Tester $left, IPv4_NetworkAddress_Tester|IPv6_NetworkAddress_Tester $right): void
    {
		try
		{
			$left->test_check_IP_version($right);
			$this->fail('An expected exception was not raised.');
		}
		catch (\InvalidArgumentException $e) {
			// We expect this
            $this->assertTrue(true);
		}
		catch (\PHPUnit\Framework\AssertionFailedError $e)
		{
			// We expect this
            $this->assertTrue(true);
		}
		catch (Exception $e) {
			$this->fail('An unexpected exception was raised.' . $e->getMessage());
		}
	}

	/**
	 * @dataProvider providerCheck_IP_version
	 */
	public function test_check_IP_version(IPv4_NetworkAddress_Tester $a4, IPv4_NetworkAddress_Tester $b4, IPv6_NetworkAddress_Tester $a6, IPv6_NetworkAddress_Tester $b6): void
    {
		try
		{
			$a4->test_check_IP_version($b4);
			$b4->test_check_IP_version($a4);

			$a6->test_check_IP_version($b6);
			$b6->test_check_IP_version($a6);
		}
		catch (Exception $e) {
			$this->fail('An unexpected exception was raised.' . $e->getMessage());
		}
        $this->assertTrue(true);
	}

	public function providerSubnets(): array
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

		foreach ($data as &$d)
		{
			$d[0] = IP\NetworkAddress::factory($d[0]);
			$d[1] = IP\NetworkAddress::factory($d[1]);
		}

		return $data;
	}

	/**
	 * @dataProvider providerSubnets
	 */
	public function testSubnets(IP\NetworkAddress $sub1, IP\NetworkAddress $sub2, bool $shares, bool $encloses): void
    {
		$this->assertEquals($shares, $sub1->shares_subnet_space($sub2));
		$this->assertEquals($encloses, $sub1->encloses_subnet($sub2));
	}

	public function providerEnclosesAddress(): array
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

		foreach ($data as &$d)
		{
			$d[0] = IP\NetworkAddress::factory($d[0]);
			$d[1] = IP\Address::factory($d[1]);
		}

		return $data;
	}

	/**
	 * @dataProvider providerEnclosesAddress
	 */
	public function testEnclosesAddress(IP\NetworkAddress $subnet, IP\Address $address, bool $expected): void
    {
		$this->assertEquals($expected, $subnet->encloses_address($address));
	}

	public function provideNetworkIdentifiers(): array
    {
		$data = array(
			array('2000::/3', true),
			array('2000::1/3', false),

			array('2000::/3', true),
			array('2000::1/3', false),
		);

		foreach ($data as &$d)
		{
			$d[0] = IP\NetworkAddress::factory($d[0]);
		}
		return $data;
	}

	/**
	 * @dataProvider provideNetworkIdentifiers
	 */
	public function testNetworkIdentifiers(IP\NetworkAddress $subnet, bool $expected): void
    {
		$this->assertEquals($expected, $subnet->is_network_identifier());
		$this->assertTrue($subnet->get_network_identifier()->is_network_identifier());
	}

	public function test__toString(): void
    {
		$ip = '192.128.1.1/24';
		$this->assertEquals($ip, (string) IP\NetworkAddress::factory($ip));

		$ip = '::1/24';
		$this->assertEquals($ip, (string) IP\NetworkAddress::factory($ip));
	}

	public function providerExcluding(): array
    {
		$data = array(
			array('192.168.0.0/24',
				array(),
				array('192.168.0.0/24')),
			array('192.168.0.0/24',
				array('192.168.0.0/25'),
				array('192.168.0.128/25')),
			array('192.168.0.0/24',
				array('192.168.0.64/26', '192.168.0.128/26'),
				array('192.168.0.0/26', '192.168.0.192/26')),
			array('192.168.0.0/24',
				array('192.168.0.0/26'),
				array('192.168.0.64/26', '192.168.0.128/25')),
			array('192.168.0.0/24',
				array('192.168.0.0/27'),
				array('192.168.0.32/27', '192.168.0.64/26', '192.168.0.128/25')),
			// Test out of range exclusions
			array('192.168.0.0/24',
				array('10.0.0.0/24'),
				array('192.168.0.0/24')),
			array('192.168.0.0/24',
				array('10.0.0.0/24', '192.168.0.0/25'),
				array('192.168.0.128/25')),
			// Test an encompassing subnet
			array('192.168.0.0/24',
				array('192.168.0.0/23'),
				array()),
		);
		foreach ($data as  &$d)
		{
			$d[0] = IP\NetworkAddress::factory($d[0]);
			for ($i=1, $iMax = count($d); $i < $iMax; $i++)
			{
				foreach ($d[$i] as &$e)
				{
					$e = IP\NetworkAddress::factory($e);
				}
			}
		}
		return $data;
	}

	/**
	 * @dataProvider providerExcluding
	 */
	public function testExcluding(IP\NetworkAddress $block, array $excluded, array $expected): void
    {
		$this->assertEquals($expected, $block->excluding($excluded));
	}

	public function provideMerge(): array
    {
		$data = array(
			// Simple merge
			array(
				array('0.0.0.0/32', '0.0.0.1/32'),
				array('0.0.0.0/31'),
			),
			// No merge
			array(
				array('0.0.0.1/32'),
				array('0.0.0.1/32'),
			),
			array(
				array('0.0.0.0/32', '0.0.0.2/32'),
				array('0.0.0.0/32', '0.0.0.2/32'),
			),
			// Duplicate entries
			array(
				array('0.0.0.0/32', '0.0.0.1/32', '0.0.0.1/32'),
				array('0.0.0.0/31'),
			),
			array(
				array('0.0.0.0/32', '0.0.0.0/32', '0.0.0.1/32'),
				array('0.0.0.0/31'),
			),
			array(
				array('0.0.0.0/32', '0.0.0.0/32', '0.0.0.1/32', '0.0.0.1/32'),
				array('0.0.0.0/31'),
			),
			// Single merge with remainder
			array(
				array('0.0.0.0/32', '0.0.0.1/32', '0.0.0.2/32'),
				array('0.0.0.2/32', '0.0.0.0/31'),
			),
			// Double merge
			array(
				array('0.0.0.0/32', '0.0.0.1/32', '0.0.0.2/31'),
				array('0.0.0.0/30'),
			),
			// Non-network identifier
			array(
				array('0.0.0.0/31', '0.0.0.3/31'),
				array('0.0.0.0/30'),
			),
			// IPv6 merges
			array(
				array('::0/128', '::1/128'),
				array('::0/127'),
			),
			array(
				array('::0/128', '::1/128', '::2/127'),
				array('::0/126'),
			),
			// Mixed subnets
			array(
				array('0.0.0.0/32', '0.0.0.1/32', '::0/128', '::1/128'),
				array('0.0.0.0/31', '::0/127'),
			),
			// Merge with duplicate resultant entry
			array(
				array('0.0.0.0/22', '0.0.0.0/24', '0.0.1.0/24', '0.0.2.0/24', '0.0.3.0/24'),
				array('0.0.0.0/22'),
			),
		);

		foreach ($data as &$x)
		{
			foreach ($x as &$y)
			{
				foreach ($y as &$addr)
				{
					$addr = IP\NetworkAddress::factory($addr);
				}
			}
		}
		return $data;
	}

	/**
	 * @dataProvider provideMerge
	 */
	public function testMerge(array $net_addrs, array $expected): void
    {
		$this->assertEquals($expected, IP\NetworkAddress::merge($net_addrs));
	}
}
