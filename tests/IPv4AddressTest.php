<?php
use Leth\IPAddress\IP, Leth\IPAddress\IPv4, Leth\IPAddress\IPv6;

class Testing_IPv4_Address extends IPv4\Address
{
	public static function factory($address)
	{
		return new Testing_IPv4\Address($address);
	}
}

/**
 * Tests for the IPv4\Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv4_Address_Test extends PHPUnit_Framework_TestCase
{

	public function providerFactory()
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
	public function testFactory($input, $expected)
	{
		$instance = IPv4\Address::factory($input);
		$this->assertNotNull($instance);
		$this->assertEquals($expected, (string) $instance);
	}
	
	public function providerFactoryException()
	{
		return array(
			array('256.0.0.1'),
			array('127.-1.0.1'),
			array('127.128.256.1'),
			array(new \Math_BigInteger('99999999999999999')),
			array(123.45),
			array(-123.45),
			array('cake'),
			array('12345'),
			array('-12345'),
			array('0000:0000:0000:ffff:0127:0000:0000:0001'),
		);
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
		$instance = IPv4\Address::factory($input);
		echo $instance->format($mode);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		IPv4\Address::factory($input);
	}

	public function providerBitwise()
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
	public function testBitwise($ip1, $ip2, $ex_and, $ex_or, $ex_xor, $ex_not)
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

	public function providerAddSubtract()
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
		
		for ($i=0; $i < count($data); $i++) { 
			$data[$i][0] = IPv4\Address::factory($data[$i][0]);
			$data[$i][2] = IPv4\Address::factory($data[$i][2]);
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
		$result = $result->subtract($right);
		$this->assertEquals(0, $result->compare_to($left));
	}

	public function providerAsIPv6Address()
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
	public function testAsIPv6Address($input, $expected_equal)
	{
		$converted = $input->as_IPv6_address();

		$this->assertInstanceOf('\Leth\IPAddress\IPv6\Address', $converted);
		$this->assertEquals(0, $converted->compare_to($expected_equal));
	}

	public function testGetOctet()
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

		$this->assertEquals(NULL, $ip->get_octet(4));
	}

	public function testArrayAccess()
	{
		$ip = IPv4\Address::factory('10.250.30.40');
		$this->assertEquals(10, $ip[-4]);
		$this->assertEquals(250, $ip[1]);

		$this->assertEquals(NULL, $ip[4]);
	}
}
