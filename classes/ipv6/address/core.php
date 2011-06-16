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

class IPv6_Address_Core extends IP_Address
{
	const ip_version = 6;

	public static function factory($address)
	{
		if (is_string($address))
		{
			if( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			{
				throw new InvalidArgumentException("'$address' is not a valid IPv6 Address.");
			}
			$address = IPv6_Address::pad($address);
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

		return new IPv6_Address($address);
	}

	/**
	 * This makes an IPv6 address fully qualified. It replaces :: with appropriate 0000 blocks, and
	 * pads out all dropped 0s
	 *
	 * IE: 2001:630:d0:: becomes 2001:0630:00d0:0000:0000:0000:0000:0000
	 *
	 * @param string $address IPv6 address to be padded
	 * @return string A fully padded string IPv6 address
	 */
	public static function pad($address)
	{
		$ipparts = explode('::', $address, 2);

		$head = $ipparts[0];
		$tail = isset($ipparts[1]) ? $ipparts[1] : array();

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

	/**
	 * Function to compact the address
	 *
	 * Strips leading zeros from prefix notation and consolidates (0000:)+ into :: where appropriate
	 * This algorithm will convert multiple 16 bit 0 blocks into :: with the following priority:
	 * <ol>
	 * 	<li>number of contiguous 0 blocks</li>
	 * 	<li>order in the address (first blocks first)</li>
	 * </ol>
	 *
	 *  Will normalise IPv6 address first.
	 * @param string $address un stripped address.
	 */
	public static function compact($address)
	{
		if ( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
				throw new InvalidArgumentException("'$address' is not a valid IPv6 Address.");

		// Check that the address is a padded address first - if not, pad it out to full length
		if (strlen($address) < 39)
		{
			$address = static::pad($address);
		}

		$parts = explode(':', $address);

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

		$end = isset($largest_zero_pos) ? $largest_zero_pos : 8;
		$addr = '';

		if ($end > 0)
		{
			$addr .= implode(':', array_slice($parts, 0, $end));
		}

		if ( isset($largest_zero_pos))
		{
			$addr .= '::';
			$addr .= implode(':', array_slice($parts, $largest_zero_pos + $largest_zero_size));
		}

		return $addr;
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
	// 	return IPv4_Address::factory($address);
	// }

	/**
	 * Add the given address to this one.
	 *
	 * @param IP_Address $other The other operand.
	 * @return IP_Address An address representing the result of the operation.
	 */
	public function add(IP_Address $other)
	{
		$this->check_types($other);
		$left = new Math_BigInteger($this->address, 256);
		$right = new Math_BigInteger($other->address, 256);
		return new IPv6_Address($left->add($right));
	}

	/**
	 * Subtract the given address from this one.
	 *
	 * @param IP_Address $other The other operand.
	 * @return IP_Address An address representing the result of the operation.
	 */
	public function subtract(IP_Address $other)
	{
		$this->check_types($other);
		$left = new Math_BigInteger($this->address, 256);
		$right = new Math_BigInteger($other->address, 256);
		return new IPv6_Address($left->subtract($right));
	}

	/**
	  * Calculates the Bitwise & (AND) of a given IP address.
	  * @param IP_Address $other is the ip to be compared against
	  * @returns IP_Address
	  */
	public function bitwise_and(IP_Address $other)
	{
		return $this->bitwise_operation('&', $other);
	}

	/**
	  * Calculates the Bitwise | (OR) of a given IP address.
	  * @param IP_Address $other is the ip to be compared against
	  * @returns IP_Address
	  */
	public function bitwise_or(IP_Address $other)
	{
		return $this->bitwise_operation('|', $other);
	}

	/**
	  * Calculates the Bitwise ^ (XOR) of a given IP address.
	  * @param IP_Address $other is the ip to be compared against
	  * @returns IP_Address
	  */
	public function bitwise_xor(IP_Address $other)
	{
		return $this->bitwise_operation('^', $other);
	}

	/**
	  * Calculates the Bitwise ~ (NOT) of a given IP address.
	  * @returns IP_Address
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
				throw new InvalidArgumentException('Unknown Operation type \''.$operation.'\'.');
				break;
		}

		return new IPv6_Address($result);
	}

	public function compare_to(IP_Address $other)
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
