<?php
require_once 'PHPUnit/Framework.php';

class TestingIPv4Address extends IPv4Address
{
	public static function factory($address)
	{
		return new TestingIPv4Address($address);
	}
	
	public function callBitwiseOperation($flag, IP_Address $other = NULL)
	{
		$this->bitwiseOperation($flag, $other);
	}
}

/**
 * Tests for the IPv4Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv4AddressTest extends PHPUnit_Framework_TestCase
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
			array(new Math_BigInteger(1),'0.0.0.1'),
			array(new Math_BigInteger(2),'0.0.0.2'),
			array(new Math_BigInteger(3),'0.0.0.3'),
			array(new Math_BigInteger(256), '0.0.1.0'),
			
		);
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($input, $output)
	{
		$instance = IPv4Address::factory($input);
		
		$this->assertNotNull($instance);
		$this->assertEquals($output, (string) $instance);
	}
	
	public function providerFactoryException()
	{
		return array(
			array('256.0.0.1'),
			array('127.-1.0.1'),
			array('127.128.256.1'),
			array(-12345),
			array(123.45),
			array(-123.45),
			array('cake'),
			array('12345'),
			array('-12345'),
			array('0000:0000:0000:ffff:0127:0000:0000:0001'),
		);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		IPv4Address::factory($input);
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
		$ip1 = IPv4Address::factory($ip1);
		$ip2 = IPv4Address::factory($ip2);
		
		$this->assertEquals($ex_and, (string) $ip1->bitwiseAND($ip2));
		$this->assertEquals($ex_or , (string) $ip1->bitwiseOR($ip2));
		$this->assertEquals($ex_xor, (string) $ip1->bitwiseXOR($ip2));
		$this->assertEquals($ex_not, (string) $ip1->bitwiseNOT());
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
	// 	$ip = IPv4Address::factory($v4);
	// 	
	// 	$this->assertEquals($v6, (string) $ip->asIPv6Address());
	// }

	public function testBitwiseException()
	{
		
		$ip = TestingIPv4Address::factory('0.0.0.1');
		
		try
		{
			$ip->callBitwiseOperation('!', $ip);
			$this->fail('An expected exception has not been raised.');
		}
		catch (InvalidArgumentException $e){}
		
		$ip->callBitwiseOperation('&', $ip);
		$ip->callBitwiseOperation('|', $ip);
		$ip->callBitwiseOperation('^', $ip);
		$ip->callBitwiseOperation('~');
	}
	
	public function providerAddSubtract()
	{
		$data = array(
			array('0.0.0.0', '0.0.0.0', '0.0.0.0'),
			array('0.0.0.0', '0.0.0.1', '0.0.0.1'),
			array('0.0.0.1', '0.0.0.0', '0.0.0.1'),
			array('0.0.0.1', '0.0.0.1', '0.0.0.2'),
			array('0.0.0.10', '0.0.0.1', '0.0.0.11'),
			array('0.0.0.255', '0.0.0.1', '0.0.1.0'),
			array('0.0.255.0', '0.0.1.1', '0.1.0.1'),
		);
		
		for ($i=0; $i < count($data); $i++) { 
			for ($j=0; $j < 3; $j++)
				$data[$i][$j] = IPv4Address::factory($data[$i][$j]);
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider providerAddSubtract
	 */
	public function testAddSubtract($left, $right, $expected)
	{
		$result = $left->add($right);
		$this->assertEquals(0, $result->compareTo($expected));
		$result = $result->subtract($right);
		$this->assertEquals(0, $result->compareTo($left));
	}
}
