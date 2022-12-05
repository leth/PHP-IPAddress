<?php
declare(strict_types=1);
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
namespace Leth\IPAddress\IPv6;
use \Leth\IPAddress\IP, \Leth\IPAddress\IPv6, \Leth\IPAddress\IPv4;

class Address extends IP\Address
{
	public const IP_VERSION         = 6;
	public const FORMAT_ABBREVIATED = 2;
	public const FORMAT_MAPPED_IPV4 = 3;
	// format mapped v4 if possible, else compact
	public const FORMAT_MAY_MAPPED_COMPACT = 4;

	public static function factory(IP\Address|int|string|\Math_BigInteger $address): IPv6\Address
	{
		if ($address instanceof self)
		{
			return $address;
		}
		elseif (is_string($address))
		{
			if ( ! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			{
				throw new \InvalidArgumentException("'$address' is not a valid IPv6 Address.");
			}
			$address = inet_pton($address);
		}
		elseif ($address instanceOf \Math_BigInteger)
		{
			// Do nothing
		}
		elseif (is_int($address))
		{
			$address = new \Math_BigInteger($address);
		}
		else
		{
			throw new \InvalidArgumentException('Unsupported argument type.');
		}

		return new IPv6\Address($address);
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
	public static function pad(string $address): string
    {
		$parts = explode(':', $address);
		$count = count($parts);

		$hextets = array();
		foreach ($parts as $i => $part)
		{
			if (isset($part[3])) // not need pad
			{
				$hextets[] = $part;
			}
			elseif ($part === '' && 0 < $i && $i < $count - 1) // missing hextets in ::
			{
				$missing = 8 - $count + 1;
				while ($missing--)
				{
					$hextets[] = '0000';
				}
			}
			else
			{
				$hextets[] = str_pad($part, 4, '0', STR_PAD_LEFT);
			}
		}

		return implode(':', $hextets);
	}

	protected function __construct(\Math_BigInteger|int|string $address)
	{
		if ($address instanceOf \Math_BigInteger)
		{
			parent::__construct(str_pad($address->abs()->toBytes(), 16, chr(0), STR_PAD_LEFT));
		}
		else
		{
			parent::__construct($address);
		}
	}

	public function is_encoded_IPv4_address(): bool
    {
		return strncmp($this->address, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff", 12) === 0;
	}

	public function as_IPv4_address(): IPv4\Address
    {
		if(!$this->is_encoded_IPv4_address())
			throw new \InvalidArgumentException('Not an IPv4 Address encoded in an IPv6 Address');
		[,$hex] = unpack('H*', $this->address);
		$parts = array_map('hexdec', array_slice(str_split($hex, 2), 12));
		$address = implode('.', $parts);
		return IPv4\Address::factory($address);
	}

	public function add($value): Address
    {
		$left = new \Math_BigInteger($this->address, 256);
		$right = ($value instanceof \Math_BigInteger) ? $value : new \Math_BigInteger($value);
		return new IPv6\Address($left->add($right));
	}

	public function subtract($value): IPv6\Address
	{
		$left = new \Math_BigInteger($this->address, 256);
		$right = ($value instanceof \Math_BigInteger) ? $value : new \Math_BigInteger($value);
		return new IPv6\Address($left->subtract($right));
	}

	/**
	  * Calculates the Bitwise & (AND) of a given IP address.
	  * @param IP\Address $other is the ip to be compared against
	  * @return IPv6\Address
	  */
	public function bitwise_and(IP\Address $other): IPv6\Address
	{
		return $this->bitwise_operation('&', $other);
	}

	/**
	  * Calculates the Bitwise | (OR) of a given IP address.
	  * @param IP\Address $other is the ip to be compared against
	  * @return IPv6\Address
	  */
	public function bitwise_or(IP\Address $other): IPv6\Address
	{
		return $this->bitwise_operation('|', $other);
	}

	/**
	  * Calculates the Bitwise ^ (XOR) of a given IP address.
	  * @param IP\Address $other is the ip to be compared against
	  * @return IPv6\Address
	  */
	public function bitwise_xor(IP\Address $other): IPv6\Address
	{
		return $this->bitwise_operation('^', $other);
	}

	/**
	  * Calculates the Bitwise ~ (NOT) of a given IP address.
	  * @return IPv6\Address
	  */
	public function bitwise_not(): IPv6\Address
	{
		return $this->bitwise_operation('~');
	}

	public function bitwise_operation(string $operation, IP\Address $other = NULL): IPv6\Address
	{
		if ($operation !== '~')
		{
			$this->check_types($other);
		}

        $result = match ($operation) {
            '&' => $other->address & $this->address,
            '|' => $other->address | $this->address,
            '^' => $other->address ^ $this->address,
            '~' => ~$this->address,
            default => throw new \InvalidArgumentException('Unknown Operation type \'' . $operation . '\'.'),
        };

		return new IPv6\Address($result);
	}

	public function compare_to(IP\Address $other): int
	{
		$this->check_types($other);

		if ($this->address < $other->address) {
            return -1;
        }
		elseif ($this->address > $other->address) {
            return 1;
        }
		else {
            return 0;
        }
	}

	public function format(int $mode): string
	{
		[, $hex] = unpack('H*', $this->address);
		$parts = str_split($hex, 4);

		if ($mode === self::FORMAT_MAY_MAPPED_COMPACT) {
			if ($this->is_encoded_IPv4_address()) {
				$mode = self::FORMAT_MAPPED_IPV4;
			} else {
				$mode = IP\Address::FORMAT_COMPACT;
			}
		}

		switch ($mode) {
			case IP\Address::FORMAT_FULL:
				// Do nothing
				break;

			case IPv6\Address::FORMAT_ABBREVIATED:
				foreach ($parts as $i => $quad)
				{
					$parts[$i] = ($quad === '0000') ? '0' : ltrim($quad, '0');
				}
				break;

			case IPv6\Address::FORMAT_MAPPED_IPV4:
				list($a, $b) = str_split($parts[6], 2);
				list($c, $d) = str_split($parts[7], 2);
				return '::ffff:' . implode('.', array(hexdec($a), hexdec($b), hexdec($c), hexdec($d)));

			case IP\Address::FORMAT_COMPACT:
				$best_pos   = $zeros_pos = FALSE;
				$best_count = $zeros_count = 0;
				foreach ($parts as $i => $quad)
				{
					$parts[$i] = ($quad === '0000') ? '0' : ltrim($quad, '0');

					if ($quad === '0000')
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
				throw new \InvalidArgumentException('Unsupported format mode: '.$mode);
		}
		
		return implode(':', $parts);
	}

	public function __toString()
	{
		return $this->format(IPv6\Address::FORMAT_MAY_MAPPED_COMPACT);
	}
}
