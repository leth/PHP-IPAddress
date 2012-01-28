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
	const IP_VERSION = 4;
	const MAX_IP = '255.255.255.255';

	public static function factory($address)
	{
		if ($address instanceof IPv4_Address)
		{
			return $address;
		}
		elseif (is_string($address))
		{
			$tmp = filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
			if ($tmp === FALSE)
				throw new InvalidArgumentException("'$address' is not a valid IPv4 Address");

			$address = $tmp;
		}
		elseif ($address instanceOf Math_BigInteger)
		{
			if ($address->compare(new Math_BigInteger(pack('N', ip2long(static::MAX_IP)), 256)) > 0)
				throw new InvalidArgumentException("IP value out of range.");
			
			$address = intval($address->toString());
		}
		elseif (is_int($address))
		{
			// Assume the input has come from ip2long
		}
		else
		{
			throw new InvalidArgumentException("Unsupported argument type.");
		}

		return new IPv4_Address($address);
	}

	public static function pad($address)
	{
		if ( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
			throw new InvalidArgumentException("'$address' is not a valid IPv4 Address.");
		$parts = array();
		foreach (explode('.', $address) as $part)
		{
			$parts[] = str_pad($part, 3, '0', STR_PAD_LEFT);
		}
		return implode('.', $parts);
	}

	public static function compact($address)
	{
		if ( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
			throw new InvalidArgumentException("'$address' is not a valid IPv4 Address.");
		// Lazy way of doing it...
		return long2ip(ip2long($address));
	}

	protected function __construct($address)
	{
		if ( ! is_int($address))
		{
			$address = ip2long($address);
		}
		parent::__construct($address);
	}

	public function add($value)
	{
		if ($value instanceof Math_BigInteger)
		{
			$value = intval( (string) $value);
		}
		return IPv4_Address::factory($this->address + $value);
	}

	public function subtract($value)
	{
		if ($value instanceof Math_BigInteger)
		{
			$value = intval( (string) $value);
		}
		return IPv4_Address::factory($this->address - $value);
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

		return IPv4_Address::factory($res);
	}
	
	/**
	 * Creates a IPv6 address object representing the 'IPv4-Mapped' IPv6 address of this object
	 *
	 * @returns IPv6_Address
	 */
	public function as_ipv6_address()
	{
		$address = str_split(str_pad(dechex($this->address), 8, '0', STR_PAD_LEFT), 4);
		$address = array_merge(array('','','ffff'), $address);
		$address = join(':', $address);

		return IPv6_Address::factory($address);
	}

	public function compare_to(IP_Address $other)
	{
		$this->check_types($other);

		return $this->address - $other->address;
	}

	public function format($mode)
	{
		switch ($mode) {
			case IP_Address::FORMAT_COMPACT:
				return long2ip($this->address);
			case IP_Address::FORMAT_FULL:
				$parts = explode('.', long2ip($this->address));
				foreach ($parts as $i => $octet) {
					$parts[$i] = str_pad($octet, 3, '0', STR_PAD_LEFT);
				}
				return implode('.', $parts);
			default:
				throw new InvalidArgumentException('Unsupported format mode: '.$mode);
		}
	}


}