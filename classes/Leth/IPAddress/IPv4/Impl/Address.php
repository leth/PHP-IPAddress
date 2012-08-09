<?php
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
namespace Leth\IPAddress\IPv4\Impl;
use \Leth\IPAddress\IP, \Leth\IPAddress\IPv4, \Leth\IPAddress\IPv6;

class Address extends IP\Address
{
	const IP_VERSION = 4;
	const MAX_IP = '255.255.255.255';

	public static function factory($address)
	{
		if ($address instanceof IPv4\Address)
		{
			return $address;
		}
		elseif (is_string($address))
		{
			$tmp = filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
			if ($tmp === FALSE)
				throw new \InvalidArgumentException("'$address' is not a valid IPv4 Address");

			$address = static::_pack(ip2long($address));
		}
		elseif ($address instanceOf \Math_BigInteger)
		{
			if ($address->compare(new \Math_BigInteger(pack('N', ip2long(static::MAX_IP)), 256)) > 0)
				throw new \InvalidArgumentException("IP value out of range.");

			$address = str_pad($address->toBytes(), 4, chr(0), STR_PAD_LEFT);
		}
		elseif (is_int($address))
		{
			$address = static::_pack($address);
		}
		else
		{
			throw new \InvalidArgumentException("Unsupported argument type.");
		}

		return new IPv4\Address($address);
	}

	protected function __construct($address)
	{
		parent::__construct($address);
	}

	protected static function _pack($address)
	{
		return pack('N', $address);
	}

	protected static function _unpack($address)
	{
		$out = unpack('N', $address);
		return $out[1];
	}

	public function add($value)
	{
		if ($value instanceof \Math_BigInteger)
		{
			$value = intval( (string) $value);
		}
		return new IPv4\Address(static::_pack(static::_unpack($this->address) + $value));
	}

	public function subtract($value)
	{
		if ($value instanceof \Math_BigInteger)
		{
			$value = intval( (string) $value);
		}
		return new IPv4\Address(static::_pack(static::_unpack($this->address) - $value));
	}

	/**
	  * Calculates the Bitwise & (AND) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwise_and(IP\Address $other)
	{
		$this->check_types($other);
		return new IPv4\Address($this->address & $other->address);
	}

	/**
	  * Calculates the Bitwise | (OR) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwise_or(IP\Address $other)
	{
		$this->check_types($other);
		return new IPv4\Address($this->address | $other->address);
	}

	/**
	  * Calculates the Bitwise ^ (XOR) of a given IP address.
	  * @param IPv4\Address $other is the ip to be compared against
	  * @returns IP\Address
	  */
	public function bitwise_xor(IP\Address $other)
	{
		$this->check_types($other);
		return new IPv4\Address($this->address ^ $other->address);
	}

	/**
	  * Calculates the Bitwise ~ (NOT) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IP\Address
	  */
	public function bitwise_not()
	{
		return new IPv4\Address(~ $this->address);
	}

	/**
	 * Creates a IPv6 address object representing the 'IPv4-Mapped' IPv6 address of this object
	 *
	 * @returns IPv6\Address
	 */
	public function as_IPv6_address()
	{
		list( , $address) = unpack('H*', $this->address);
		$address = join(':', str_split($address, 4));
		$address = '::ffff:'.$address;

		return IPv6\Address::factory($address);
	}

	public function compare_to(IP\Address $other)
	{
		$this->check_types($other);

		if ($this->address < $other->address)
			return -1;
		elseif ($this->address > $other->address)
			return 1;
		else
			return 0;
	}

	public function format($mode)
	{
		$address = static::_unpack($this->address);
		switch ($mode) {
			case IP\Address::FORMAT_COMPACT:
				return long2ip($address);
			case IP\Address::FORMAT_FULL:
				$parts = explode('.', long2ip($address));
				foreach ($parts as $i => $octet) {
					$parts[$i] = str_pad($octet, 3, '0', STR_PAD_LEFT);
				}
				return implode('.', $parts);
			default:
				throw new \InvalidArgumentException('Unsupported format mode: '.$mode);
		}
	}


}