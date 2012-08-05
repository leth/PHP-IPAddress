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

class IPv6_Address_Core extends IP_Address
{
	const IP_VERSION = 6;
	const FORMAT_ABBREVIATED = 2;

	public static function factory($address)
	{
		if ($address instanceof IPv6_Address)
		{
			return $address;
		}
		elseif (is_string($address))
		{
			if ( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
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
		elseif (is_int($address))
		{
			$address = new Math_BigInteger($address);
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

		return implode(':', $ippad);
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
	// 		throw InvalidArgumentException('Not an IPv4 Address encoded in an IPv6 Address');
	// 
	// 	$address = implode('.', array_map('intval', explode(':', $matches[1])));
	// 
	// 	return IPv4_Address::factory($address);
	// }

	public function add($value)
	{
		$left = new Math_BigInteger($this->address, 256);
		$right = ($value instanceof Math_BigInteger) ? $value : new Math_BigInteger($value);
		return new IPv6_Address($left->add($right));
	}

	public function subtract($value)
	{
		$left = new Math_BigInteger($this->address, 256);
		$right = ($value instanceof Math_BigInteger) ? $value : new Math_BigInteger($value);
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

	public function format($mode)
	{
		list(,$hex) = unpack('H*', $this->address);
		$parts = str_split($hex, 4);
		
		switch ($mode) {
			case IP_Address::FORMAT_FULL:
				// Do nothing
				break;

			case IPv6_Address::FORMAT_ABBREVIATED:
				foreach ($parts as $i => $quad)
				{
					$parts[$i] = ($quad == '0000') ? '0' : ltrim($quad, '0');
				}
				break;

			case IP_Address::FORMAT_COMPACT:
				$best_pos   = $zeros_pos = FALSE;
				$best_count = $zeros_count = 0;
				foreach ($parts as $i => $quad)
				{
					$parts[$i] = ($quad == '0000') ? '0' : ltrim($quad, '0');

					if ($quad == '0000')
					{
						if ($zeros_pos === FALSE)
						{
							$zeros_pos = $i;
						}
						$zeros_count++;
						
						if ($zeros_count > $best_count)
						{
							$best_count = $zeros_count;
							$best_pos = $zeros_pos;
						}
					}
					else
					{
						$zeros_count = 0;
						$zeros_pos = FALSE;
						
						$parts[$i] = ltrim($quad, '0');
					}
				}

				
				if ($best_pos !== FALSE)
				{
					$insert = array(NULL);
					
					if ($best_pos == 0 OR $best_pos + $best_count == 8)
					{
						$insert[] = NULL;
						if ($best_count == count($parts))
						{
							$best_count--;
						}
					}
					array_splice($parts, $best_pos, $best_count, $insert);
				}
				
				break;

			default:
				throw new InvalidArgumentException('Unsupported format mode: '.$mode);
		}
		
		return implode(':', $parts);
	}

}
