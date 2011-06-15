<?php defined('SYSPATH') or die('No direct script access.');
/*
 * This file is part of the PHP-IPAddress library.
 *
 * The PHP-IPAddress library is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * The PHP-IPAddress library is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with the PHP-IPAddress library.
 * If not, see <http://www.gnu.org/licenses/>.
 */

class IPv4_Address_Core extends IP_Address
{
	const ip_version = 4;

	public static function factory($address)
	{
		if (is_string($address))
		{
			if( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
				throw new InvalidArgumentException("'$address' is not a valid IPv4 Address");
		}
		else if ($address instanceOf Math_BigInteger)
		{
			$address = intval($address->toString());
		}
		else if (is_int($address))
		{
			if ($address < 0)
				throw new InvalidArgumentException("Argument out of range.");
		}
		else
		{
			throw new InvalidArgumentException("Unsupported argument type.");
		}

		return new IPv4_Address($address);
	}

	protected function __construct($address)
	{
		if (is_int($address))
			parent::__construct($address);
		else
			parent::__construct(ip2long($address));
	}

	/**
	 * Add the given address to this one.
	 *
	 * @param IPAddress $other The other operand.
	 * @return IPAddress An address representing the result of the operation.
	 */
	public function add(IP_Address $other)
	{
		$this->check_types($other);
		return IPv4_Address::factory($this->address + $other->address);
	}

	/**
	 * Subtract the given address from this one.
	 *
	 * @param IPAddress $other The other operand.
	 * @return IPAddress An address representing the result of the operation.
	 */
	public function subtract(IP_Address $other)
	{
		$this->check_types($other);
		return IPv4_Address::factory($this->address - $other->address);
	}

	/**
	  * Calculates the Bitwise & (AND) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwise_and(IP_Address $other)
	{
		return $this->bitwise_operation('&', $other);
	}

	/**
	  * Calculates the Bitwise | (OR) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwise_or(IP_Address $other)
	{
		return $this->bitwise_operation('|', $other);
	}

	/**
	  * Calculates the Bitwise ^ (XOR) of a given IP address.
	  * @param IPv4_Address $other is the ip to be compared against
	  * @returns IP_Address
	  */
	public function bitwise_xor(IP_Address $other)
	{
		return $this->bitwise_operation('^', $other);
	}

	/**
	  * Calculates the Bitwise ~ (NOT) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IP_Address
	  */
	public function bitwise_not()
	{
		return $this->bitwise_operation('~');
	}

	protected function bitwise_operation($operation)
	{
		$args = func_get_args();
		$operation = array_shift($args);
		array_unshift($args, $this);

		$addr[0] = $args[0]->address;
		if ($operation != '~')
		{
			$this->check_types($args[1]);
			$addr[1] = $args[1]->address;
		}

		switch ($operation) {
			case '&':
				$res = $addr[1] & $addr[0];
				break;
			case '|':
				$res = $addr[1] | $addr[0];
				break;
			case '^':
				$res = $addr[1] ^ $addr[0];
				break;
			case '~':
				$res = ~ $addr[0];
				break;

			default:
				throw new InvalidArgumentException("Unknown operation flag '$operation'.");
				break;
		}

		return IPv4_Address::factory(long2ip($res));
	}
	// TODO Check this
	// public function as_IPv6_Address()
	// {
	// 	$address = str_replace('.',':','0000:0000:0000:ffff:' . $this);
	//
	// 	return IPv6Address::factory($address);
	// }

	public function compare_to(IP_Address $other)
	{
		$this->check_types($other);

		return $this->address - $other->address;
	}

	public function address()
	{
		return long2ip($this->address);
	}


}