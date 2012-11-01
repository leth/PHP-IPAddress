<?php

class TestingIPv6_Address extends IPv6_Address
{
	public static function factory($address)
	{
		return new TestingIPv6_Address($address);
	}
	
	public function call_bitwise_operation($flag, IP_Address $other = NULL)
	{
		$this->bitwise_operation($flag, $other);
	}
}

/**
 * Tests for the IPv6_Address Class
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
		$instance = IPv6_Address::factory($input);
		
		$this->assertNotNull($instance);
		$this->assertEquals($compact, $instance->format(IP_Address::FORMAT_COMPACT));
		$this->assertEquals($abbr, $instance->format(IPv6_Address::FORMAT_ABBREVIATED));
		$this->assertEquals($full, $instance->format(IP_Address::FORMAT_FULL));
	}

	public function providerFormatException()
	{
		$bad_mode = -1;
		$data = self::providerFactory();
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
		$instance = IPv6_Address::factory($input);
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
		IPv6_Address::factory($input);
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
			array('::10', new Math_BigInteger(1), '::11' ),
			array('::10', new Math_BigInteger(2), '::12' ),
		);
		
		for ($i=0; $i < count($data); $i++)
		{
			$data[$i][0] = IPv6_Address::factory($data[$i][0]);
			$data[$i][2] = IPv6_Address::factory($data[$i][2]);
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
			$data[$i][0] = IPv6_Address::factory($data[$i][0]);
			$data[$i][1] = IPv6_Address::factory($data[$i][1]);
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
				$data[$i][$j] = IPv6_Address::factory($data[$i][$j]);
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
	// public function provider_as_IPv4_Address()
	// {
	// 	return array(
	// 		array('0000:0000:0000:ffff:0127:0000:0000:0001', '127.0.0.1'),
	// 	);
	// }
	// 
	// /**
	//  * @dataProvider provider_as_IPv4_Address
	//  */
	// public function test_as_IPv4_Address($v6, $v4 = NULL)
	// {
	// 	$ip = new IPv6_Address($v6);
	// 	
	// 	if ($v4 === NULL)
	// 		$this->assertFalse($ip->isEncodedIPv4Address());
	// 	else
	// 		$this->assertEquals($v4, (string) $ip->asIPv4Address());
	// 	
	// }
}
