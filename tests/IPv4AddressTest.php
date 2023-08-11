<?php
declare(strict_types=1);
use Leth\IPAddress\IPv4, Leth\IPAddress\IPv6;
use Leth\IPAddress\IPv6\Address;
use PHPUnit\Framework\TestCase;

class Testing_IPv4_Address extends IPv4\Address
{
	public static function factory($address): Testing_IPv4_Address
	{
		return new Testing_IPv4_Address($address);
	}
}

/**
 * Tests for the IPv4\Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv4_Address_Test extends TestCase
{

	public function providerFactory(): array
	{
		return array(
			array('127.0.0.1',  '127.0.0.1'  ),
			array('127.0.0.0',  '127.0.0.0'  ),
			array('127.0.0.2',  '127.0.0.2'  ),
			array('192.168.1.1','192.168.1.1'),
			array('192.168.2.1','192.168.2.1'),
			array('192.168.1.2','192.168.1.2'),
			array('10.0.0.2',   '10.0.0.2'   ),
			array('10.0.0.1',   '10.0.0.1'   ),
			array(new \Math_BigInteger(1),'0.0.0.1'),
			array(new \Math_BigInteger(2),'0.0.0.2'),
			array(new \Math_BigInteger(3),'0.0.0.3'),
			array(new \Math_BigInteger(256), '0.0.1.0'),
		);
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory(mixed $input, mixed $expected): void
	{
		$instance = IPv4\Address::factory($input);
		$this->assertNotNull($instance);
		$this->assertEquals($expected, (string) $instance);
	}

	public function providerFactoryException(): array
	{
		return array(
			array('256.0.0.1'),
			array('127.-1.0.1'),
			array('127.128.256.1'),
			array(new \Math_BigInteger('99999999999999999')),
			#array(123.45), throws TypeError
			#array(-123.45), throws TypeError
			array('cake'),
			array('12345'),
			array('-12345'),
			array('0000:0000:0000:ffff:0127:0000:0000:0001'),
		);
	}

	public function testFormatInteger(): void
	{
		$ip = IPv4\Address::factory('127.0.0.1');
		$this->assertEquals(2130706433, $ip->format(IPv4\Address::FORMAT_INTEGER));
	}

	public function providerFormatException(): array
	{
		$bad_mode = -1;
		$data = $this->providerFactory();
		foreach ($data as $i => $entry) {
			$data[$i] = array($entry[0], $bad_mode);
		}

		return $data;
	}

	/**
	 *
	 * @dataProvider providerFormatException
	 */
	public function testFormatException(mixed $input, mixed $mode): void
	{
		$this->expectException(InvalidArgumentException::class);
		$instance = IPv4\Address::factory($input);
		echo $instance->format($mode);
	}

	/**
	 *
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException(mixed $input): void
	{
		$this->expectException(InvalidArgumentException::class);
		IPv4\Address::factory($input);
	}

	public function providerBitwise(): array
	{
		return array(
			//       OP1        OP2        AND        OR         XOR        NOT
			array('0.0.0.1', '0.0.0.1', '0.0.0.1', '0.0.0.1', '0.0.0.0', '255.255.255.254'),
			array('0.0.0.0', '0.0.0.1', '0.0.0.0', '0.0.0.1', '0.0.0.1', '255.255.255.255'),
			array('0.0.0.1', '0.0.0.0', '0.0.0.0', '0.0.0.1', '0.0.0.1', '255.255.255.254'),
			array('0.0.0.0', '0.0.0.0', '0.0.0.0', '0.0.0.0', '0.0.0.0', '255.255.255.255'),
		);
	}

	/**
	 * @dataProvider providerBitwise
	 */
	public function testBitwise(string $ip1, string $ip2, string $ex_and, string $ex_or, string $ex_xor, string $ex_not): void
	{
		$ip1 = IPv4\Address::factory($ip1);
		$ip2 = IPv4\Address::factory($ip2);

		$this->assertEquals($ex_and, (string) $ip1->bitwise_and($ip2));
		$this->assertEquals($ex_or , (string) $ip1->bitwise_or($ip2));
		$this->assertEquals($ex_xor, (string) $ip1->bitwise_xor($ip2));
		$this->assertEquals($ex_not, (string) $ip1->bitwise_not());
	}

	// TODO Check this
	// public function providerAsIPv6Address()
	// {
	// 	return array(
	// 		array('127.0.0.1', '0000:0000:0000:ffff:0127:0000:0000:0001'),
	// 	);
	// }
	//
	// /**
	//  * @dataProvider providerAsIPv6Address
	//  */
	// public function testAsIPv6Address($v4, $v6)
	// {
	// 	$ip = IPv4\Address::factory($v4);
	//
	// 	$this->assertEquals($v6, (string) $ip->asIPv6Address());
	// }

	public function providerAddSubtract(): array
	{
		$data = array(
			array('0.0.0.0'  , 0, '0.0.0.0'),
			array('0.0.0.0'  , 1, '0.0.0.1'),
			array('0.0.0.1'  , 0, '0.0.0.1'),
			array('0.0.0.1'  , 1, '0.0.0.2'),
			array('0.0.0.10' , 1, '0.0.0.11'),
			array('0.0.0.255', 1, '0.0.1.0'),
			array('0.0.255.0', 257, '0.1.0.1'),
			array('255.255.0.0'  , 0, '255.255.0.0'),
			array('255.255.0.0'  , 1, '255.255.0.1'),
			array('255.255.0.1'  , 0, '255.255.0.1'),
			array('255.255.0.1'  , 1, '255.255.0.2'),
			array('255.255.0.10' , 1, '255.255.0.11'),
			array('255.255.0.255', 1, '255.255.1.0'),
			array('255.0.255.0', 257, '255.1.0.1'),
			array('192.168.0.0', 4, '192.168.0.4'),
		);

		for ($i=0, $iMax = count($data); $i < $iMax; $i++) {
			$data[$i][0] = IPv4\Address::factory($data[$i][0]);
			$data[$i][2] = IPv4\Address::factory($data[$i][2]);
		}

		return $data;
	}

	/**
	 * @dataProvider providerAddSubtract
	 */
	public function testAddSubtract(IPv4\Address $left, int $right, IPv4\Address $expected): void
	{
		$result = $left->add($right);
		$this->assertEquals(0, $result->compare_to($expected));
		$result = $result->subtract($right);
		$this->assertEquals(0, $result->compare_to($left));
	}

	public function providerAsIPv6Address(): array
	{
		$data = array(
			array('0.0.0.0'  , '::ffff:0:0'   ),
			array('0.0.0.1'  , '::ffff:0:1'   ),
			array('0.0.0.255', '::ffff:0:ff'  ),
			array('0.0.255.0', '::ffff:0:ff00'),
			array('0.255.0.0', '::ffff:ff:0'  ),
			array('255.0.0.0', '::ffff:ff00:0'),
		);

		foreach ($data as $i => $entry) {
			$data[$i] = array(
				IPv4\Address::factory($entry[0]),
				IPv6\Address::factory($entry[1]));
		}

		return $data;
	}

	/**
	 * @dataProvider providerAsIPv6Address
	 */
	public function testAsIPv6Address(IPv4\Address $input, IPv6\Address $expected_equal): void
	{
		$converted = $input->as_IPv6_address();

		$this->assertInstanceOf(Address::class, $converted);
		$this->assertEquals(0, $converted->compare_to($expected_equal));
	}

	public function testGetOctet(): void
	{
		$ip = IPv4\Address::factory('10.250.30.40');
		$this->assertEquals(10, $ip->get_octet(-4));
		$this->assertEquals(250, $ip->get_octet(-3));
		$this->assertEquals(30, $ip->get_octet(-2));
		$this->assertEquals(40, $ip->get_octet(-1));
		$this->assertEquals(10, $ip->get_octet(0));
		$this->assertEquals(250, $ip->get_octet(1));
		$this->assertEquals(30, $ip->get_octet(2));
		$this->assertEquals(40, $ip->get_octet(3));

		$this->assertNull($ip->get_octet(4));
	}

	public function testArrayAccess(): void
	{
		$ip = IPv4\Address::factory('10.250.30.40');
		$this->assertEquals(10, $ip[-4]);
		$this->assertEquals(250, $ip[1]);
		$this->assertNotEmpty($ip[1]);

		$this->assertNull($ip[4]);
		$this->assertFalse(isset($ip[4]));
	}

	public function testArrayAccessSet(): void
	{
		$this->expectException(\LogicException::class);
		$ip = IPv4\Address::factory('10.250.30.40');
		$ip[0] = 0;
	}

	public function testArrayAccessUnset(): void
	{
		$this->expectException(\LogicException::class);
		$ip = IPv4\Address::factory('10.250.30.40');
		unset($ip[0]);
	}
}
