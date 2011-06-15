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

class Ipv6_Address_Core extends Ip_Address
{
	const ip_version = 6;

	public static function factory($address)
	{
		if (is_string($address))
		{
			if( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
				throw new InvalidArgumentException("'$address' is not a valid IPv6 Address.");

			$address = Ipv6_Address::pad($address);
			$address = pack('H*', str_replace(':', '', $address));
		}
		elseif ($address instanceOf Math_BigInteger)
		{
			// Do nothing
		}
		else
		{
			throw new InvalidArgumentException('Unsupported argument type.');
		}

		return new Ipv6_Address($address);
	}

	protected function __construct($address)
	{
		if ($address instanceOf Math_BigInteger)
		{
			parent::__construct(str_pad($address->abs()->toBytes(), 16, chr(0), STR_PAD_LEFT));
		}
		else
		{
			parent::__construct($address);
		}
	}

	/**
	 * This makes an IPv6 address fully qualified. It replaces :: with appropriate 0000 blocks, and
	 * pads out all dropped 0s
	 *
	 * IE: 2001:630:d0:: becomes 2001:0630:00d0:0000:0000:0000:0000:0000
	 *
	 * @param string $address IPv6 address to be padded
	 * @return string A fully padded string ipv6 address
	 */
	public static function pad($address)
	{
		if( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
				throw new InvalidArgumentException("'$address' is not a valid IPv6 Address.");
		$ipparts = explode('::', $address, 2);
		$head = $ipparts[0];
		if (isset($ipparts[1]))
		{
			$tail = $ipparts[1];
		}
		else
		{
			$tail = array();
		}

		$headparts = explode(':', $head);
		$ippad = array();
		foreach ($headparts as $val)
		{
			$ippad[] = str_pad($val, 4, '0', STR_PAD_LEFT);
		}
		if (count($ipparts) > 1)
		{
			$tailparts = explode(':', $tail);
			$midparts = 8 - count($headparts) - count($tailparts);

			for ($i=0; $i < $midparts; $i++)
			{
				$ippad[] = '0000';
			}

			foreach ($tailparts as $val)
			{
				$ippad[] = str_pad($val, 4, '0', STR_PAD_LEFT);
			}
		}

		return join(':', $ippad);
	}

	/** Function to strip the address
	 *
	 * Strips leading zeros from prefix notation and consolidates (0000:)+ into :: where appropriate
	 * This algorithm will conert multiple 16 bit 0 blocks into :: with the following priority:
	 * <ol>
	 * 	<li>number of contiguous 0 blocks</li>
	 *  <li>order in the address (first blocks first)</li>
	 *  </ol>
	 *
	 *  Will normallize v6 address first.
	 * @param string $address un stripped address.
	 */
	public static function strip($address)
	{
		if ( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
				throw new InvalidArgumentException("'$address' is not a valid IPv6 Address.");

		// Check that the address is a padded address first - if not, pad it out to full length
		if (strlen($address) < 39)
		{
			$address = self::pad($address);
		}

		$parts = explode(':', $address);
		$size_of_zeros[0] = 0;

		$largest_zero_pos = NULL;
		$largest_zero_size = 0;

		$current_zero_pos = NULL;
		$current_zero_size = 0;


		foreach ($parts as $k => $part)
		{
			if ($part == '0000')
			{
				// Contender for largest group
				if ( ! isset($current_zero_pos))
				{
					$current_zero_pos = $k;
				}
				$current_zero_size++;
				if ($largest_zero_size < $current_zero_size)
				{
					$largest_zero_size = $current_zero_size;
					$largest_zero_pos = $current_zero_pos;
				}
				$parts[$k] = '0';
			}
			else
			{
				// Remove left zeros
				unset ($current_zero_pos);
				$current_zero_size = 0;
				$parts[k] = ltrim($part, '0');
			}
		}

		if ( ! isset($largest_zero_pos))
		{
			$i = 8;
		}
		else
		{
			$i = $largest_zero_pos;
		}

		$addr = '';

		if ($i > 0)
		{
			$addr .= implode(':', array_slice($parts, 0, $i));
		}

		if ( isset($largest_zero_size))
		{
			$addr .= '::';
			$addr .= implode(':', array_slice($parts, $largest_zero_size + $largest_zero_pos));
		}

	}

	// TODO Add support for NAT64 addresses
	// TODO fix this.
	// public function is_encoded_ipv4_address()
	// {
	// 	$address = (string) $this;
	// 	return preg_match('#^0000:0000:0000:ffff:(0\d{1,3}\.0\d{1,3}\.0\d{1,3}\.0\d{1,3})$#','\1', $address) != 0;
	// }
	//
	// public function as_ipv4_address()
	// {
	// 	$address = (string) $this;
	// 	$match_count = preg_match('#^0000:0000:0000:ffff:(0\d{1,3}\.0\d{1,3}\.0\d{1,3}\.0\d{1,3})$#','\1', $address, $matches);
	//
	// 	if ($match_count == 0)
	// 		throw new Exception('Not an IPv4 Address encoded in an IPv6 Address');
	//
	// 	$address = join('.', array_map('intval', explode(':', $matches[1])));
	//
	// 	return Ipv4_Address::factory($address);
	// }

	/**
	 * Add the given address to this one.
	 *
	 * @param Ip_Address $other The other operand.
	 * @return Ip_Address An address representing the result of the operation.
	 */
	public function add(Ip_Address $other)
	{
		$this->check_types($other);
		$left = new Math_BigInteger($this->address, 256);
		$right = new Math_BigInteger($other->address, 256);
		return Ipv6_Address::factory($left->add($right));
	}

	/**
	 * Subtract the given address from this one.
	 *
	 * @param Ip_Address $other The other operand.
	 * @return Ip_Address An address representing the result of the operation.
	 */
	public function subtract(Ip_Address $other)
	{
		$this->checkTypes($other);
		$left = new Math_BigInteger($this->address, 256);
		$right = new Math_BigInteger($other->address, 256);
		return Ipv6_Address::factory($left->subtract($right));
	}

	/**
	  * Calculates the Bitwise & (AND) of a given IP address.
	  * @param Ip_Address $other is the ip to be compared against
	  * @returns Ip_Address
	  */
	public function bitwise_and(Ip_Address $other)
	{
		return $this->bitwise_operation('&', $other);
	}

	/**
	  * Calculates the Bitwise | (OR) of a given IP address.
	  * @param Ip_Address $other is the ip to be compared against
	  * @returns Ip_Address
	  */
	public function bitwise_or(Ip_Address $other)
	{
		return $this->bitwiseOperation('|', $other);
	}

	/**
	  * Calculates the Bitwise ^ (XOR) of a given IP address.
	  * @param Ip_Address $other is the ip to be compared against
	  * @returns Ip_Address
	  */
	public function bitwise_xor(Ip_Address $other)
	{
		return $this->bitwise_operation('^', $other);
	}

	/**
	  * Calculates the Bitwise ~ (NOT) of a given IP address.
	  * @returns Ip_Address
	  */
	public function bitwise_not()
	{
		return $this->bitwise_operation('~');
	}

	public function bitwise_operation($operation, $other = NULL)
	{
		if ($operation != '~')
		{
			$this->check_types($other);
		}

		switch ($operation) {
			case '&':
				$result = $other->address & $this->address;
				break;
			case '|':
				$result = $other->address | $this->address;
				break;
			case '^':
				$result = $other->address ^ $this->address;
				break;
			case '~':
				$result = ~$this->address;
				break;

			default:
				throw new InvalidArgumentException('Unknown Operation.');
				break;
		}

		return Ipv6_Address::factory($result);
	}

	public function compare_to(Ip_Address $other)
	{
		$this->check_types($other);

		if ($this->address < $other->address)
			return -1;
		elseif ($this->address > $other->address)
			return 1;
		else
			return 0;
	}
	public function address()
	{
		$tmp = unpack('H*', $this->address);

		return join(':', str_split($tmp[1], 4));
	}

}
