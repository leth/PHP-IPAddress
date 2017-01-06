<?php
use Leth\IPAddress\IP, Leth\IPAddress\IPv4, Leth\IPAddress\IPv6;

class TestingIPv6_Address extends IPv6\Address
{
	public static function factory($address)
	{
		return new TestingIPv6_Address($address);
	}
	
	public function call_bitwise_operation($flag, IP\Address $other = NULL)
	{
		$this->bitwise_operation($flag, $other);
	}
}

/**
 * Tests for the IPv6\Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6_Address_Test extends PHPUnit_Framework_TestCase
{

	public function providerFactory()
	{
		return array(
			array(
				'::1',
				'::1',
				'0:0:0:0:0:0:0:1',
				'0000:0000:0000:0000:0000:0000:0000:0001'),
			array(
				1,
				'::1',
				'0:0:0:0:0:0:0:1',
				'0000:0000:0000:0000:0000:0000:0000:0001'),
			array(
				'fe80::226:bbff:fe14:7372',
				'fe80::226:bbff:fe14:7372',
				'fe80:0:0:0:226:bbff:fe14:7372',
				'fe80:0000:0000:0000:0226:bbff:fe14:7372'),
			array(
				'::ffff:127:0:0:1',
				'::ffff:127:0:0:1',
				'0:0:0:ffff:127:0:0:1',
				'0000:0000:0000:ffff:0127:0000:0000:0001'),
		);
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($input, $compact, $abbr, $full)
	{
		$instance = IPv6\Address::factory($input);

		$this->assertNotNull($instance);
		$this->assertEquals($compact, $instance->format(IP\Address::FORMAT_COMPACT));
		$this->assertEquals($abbr, $instance->format(IPv6\Address::FORMAT_ABBREVIATED));
		$this->assertEquals($full, $instance->format(IP\Address::FORMAT_FULL));
	}

	public function providerFormatException()
	{
		$bad_mode = -1;
		$data = static::providerFactory();
		foreach ($data as $i => $entry) {
			$data[$i] = array($entry[0], $bad_mode);
		}

		return $data;
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider providerFormatException
	 */
	public function testFormatException($input, $mode)
	{
		$instance = IPv6\Address::factory($input);
		echo $instance->format($mode);
	}

	public function providerFactoryException()
	{
		return array(
			array('256.0.0.1'),
			array('127.-1.0.1'),
			array('127.128.256.1'),
			array('cake'),
			array('12345'),
			array('-12345'),
			array('0000:0000:0000:ffff:0127:0000:0000:000g'),
			array('000000000000ffff0127000000000001'),
			array(array()),
		);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		IPv6\Address::factory($input);
	}

	public function providerAddSubtract()
	{
		$data = array(
			array('::'  , 0, '::' ),
			array('::1' , 0, '::1' ),
			array('::1' , 1, '::2' ),
			array('::1' , 2, '::3' ),
			array('::5' , 6, '::b' ),
			array('::10', 1, '::11' ),
			array('::10', new \Math_BigInteger(1), '::11' ),
			array('::10', new \Math_BigInteger(2), '::12' ),
		);

		for ($i=0; $i < count($data); $i++)
		{
			$data[$i][0] = IPv6\Address::factory($data[$i][0]);
			$data[$i][2] = IPv6\Address::factory($data[$i][2]);
		}
		return $data;
	}

	/**
	 * @dataProvider providerAddSubtract
	 */
	public function testAddSubtract($left, $right, $expected)
	{
		$result = $left->add($right);
		$this->assertEquals(0, $result->compare_to($expected));
		$again = $result->subtract($right);
		$this->assertEquals(0, $again->compare_to($left));
	}

	public function providerCompareTo()
	{
		$data = array(
			array('::', '::', 0),
			array('::1', '::1', 0),
			array('::1', '::2', -1),
			array('::2', '::1', 1),
			array('::f', '::1', 1),
			array('::a', '::b', -1),
		);

		for ($i=0; $i < count($data); $i++){
			$data[$i][0] = IPv6\Address::factory($data[$i][0]);
			$data[$i][1] = IPv6\Address::factory($data[$i][1]);
		}
		return $data;
	}

	/**
	 * @dataProvider providerCompareTo
	 */
	public function testCompareTo($left, $right, $expected)
	{
		$this->assertEquals($expected, $left->compare_to($right));
	}

	public function providerBitwise()
	{
		$data = array(
			//     OP1    OP2    AND    OR     XOR     NOT
			array('::1', '::1', '::1', '::1', '::0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'),
			array('::' , '::1', '::0', '::1', '::1', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'),
			array('::1', '::' , '::0', '::1', '::1', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'),
			array('::' , '::' , '::0', '::0', '::0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'),
		);

		for ($i=0; $i < count($data); $i++) {
			for ($j=0; $j < 6; $j++) {
				$data[$i][$j] = IPv6\Address::factory($data[$i][$j]);
			}
		}

		return $data;
	}

	/**
	 * @dataProvider providerBitwise
	 */
	public function testBitwise($ip1, $ip2, $ex_and, $ex_or, $ex_xor, $ex_not)
	{
		$this->assertEquals((string) $ex_and, (string) $ip1->bitwise_and($ip2));
		$this->assertEquals((string) $ex_or , (string) $ip1->bitwise_or($ip2));
		$this->assertEquals((string) $ex_xor, (string) $ip1->bitwise_xor($ip2));
		$this->assertEquals((string) $ex_not, (string) $ip1->bitwise_not());
	}

	public function testBitwiseException()
	{

		$ip = TestingIPv6_Address::factory('::1');

		try
		{
			$ip->call_bitwise_operation('!', $ip);
			$this->fail('An expected exception has not been raised.');
		}
		catch (InvalidArgumentException $e){}

		$ip->call_bitwise_operation('&', $ip);
		$ip->call_bitwise_operation('|', $ip);
		$ip->call_bitwise_operation('^', $ip);
		$ip->call_bitwise_operation('~');
	}

	//
	// public function provider_as_IPv4\Address()
	// {
	// 	return array(
	// 		array('0000:0000:0000:ffff:0127:0000:0000:0001', '127.0.0.1'),
	// 	);
	// }
	//
	// /**
	//  * @dataProvider provider_as_IPv4\Address
	//  */
	// public function test_as_IPv4\Address($v6, $v4 = NULL)
	// {
	// 	$ip = new IPv6\Address($v6);
	//
	// 	if ($v4 === NULL)
	// 		$this->assertFalse($ip->isEncodedIPv4Address());
	// 	else
	// 		$this->assertEquals($v4, (string) $ip->asIPv4Address());
	//
	// }

	public function testGetOctet()
	{
		$ip = IPv6\Address::factory('0001:0002:aaaa:1234:abcd:1000:2020:fffe');
		$this->assertEquals(0, $ip->get_octet(-16));
		$this->assertEquals(1, $ip->get_octet(-15));
		$this->assertEquals(0xAA, $ip->get_octet(-11));
		$this->assertEquals(0x12, $ip->get_octet(-10));
		$this->assertEquals(0x20, $ip->get_octet(-4));
		$this->assertEquals(0x20, $ip->get_octet(-3));
		$this->assertEquals(0xFF, $ip->get_octet(-2));
		$this->assertEquals(0xFE, $ip->get_octet(-1));
		$this->assertEquals(0, $ip->get_octet(0));
		$this->assertEquals(1, $ip->get_octet(1));
		$this->assertEquals(0x10, $ip->get_octet(10));
		$this->assertEquals(0xFE, $ip->get_octet(15));

		$this->assertEquals(NULL, $ip->get_octet(16));
	}

	public function testMappedIPv4()
	{
		$ip = IP\Address::factory('::ffff:141.44.23.50');
		$this->assertEquals(1, $ip->is_encoded_IPv4_address());
		$ipv4 = $ip->as_IPv4_address();
		$this->assertEquals($ipv4->format(IP\Address::FORMAT_COMPACT), '141.44.23.50');
	}

	public function testArrayAccess()
	{
		$ip = IPv6\Address::factory('0001:0002:aaaa:1234:abcd:1000:2020:fffe');
		$this->assertEquals(0x12, $ip[-10]);
		$this->assertEquals(0x10, $ip[10]);

		$this->assertEquals(NULL, $ip[16]);
	}

	/**
	 * @return array
	 */
	public function providerPadIps()
	{
		return array(
			array('::', '0000:0000:0000:0000:0000:0000:0000:0000'),
			array('::fff', '0000:0000:0000:0000:0000:0000:0000:0fff'),
			array('::ff:fff', '0000:0000:0000:0000:0000:0000:00ff:0fff'),
			array('::f:ff:fff', '0000:0000:0000:0000:0000:000f:00ff:0fff'),
			array('fff::', '0fff:0000:0000:0000:0000:0000:0000:0000'),
			array('fff:ff::', '0fff:00ff:0000:0000:0000:0000:0000:0000'),
			array('fff:ff:f::', '0fff:00ff:000f:0000:0000:0000:0000:0000'),
			array('2001:630:d0::', '2001:0630:00d0:0000:0000:0000:0000:0000'),
			array('f:f:f:f:f:f:f:f', '000f:000f:000f:000f:000f:000f:000f:000f'),
			array('fff::fff', '0fff:0000:0000:0000:0000:0000:0000:0fff'),
			array('fff:0000:bb::aa:0000:fff', '0fff:0000:00bb:0000:0000:00aa:0000:0fff'),
			// not need pad
			array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'),
			array('0000:0000:0000:0000:0000:0000:0000:0000', '0000:0000:0000:0000:0000:0000:0000:0000'),
		);
	}

	/**
	 * @dataProvider providerPadIps
	 *
	 * @param string $actual
	 * @param string $expected
	 */
	public function testPad($actual, $expected)
	{
		$this->assertEquals($expected, IPv6\Address::pad($actual));
	}
}
