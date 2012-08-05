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

/**
 * An abstract representation of an IP Address in a given network
 *
 * @package default
 * @author Marcus Cobden
 */
abstract class IP_Network_Address_Core
{
	const IP_VERSION = -1;
	const MAX_SUBNET = -1;

	/**
	 * The IP Address
	 *
	 * @var IPAddress
	 */
	protected $address;

	/**
	 * The CIDR number
	 *
	 * @var int
	 */
	protected $cidr;

	/**
	 * Generates the subnet mask for a given CIDR
	 *
	 * @param int $cidr The CIDR number
	 * @return IP_Address An IP address representing the mask.
	 */
	public static function generate_subnet_mask($cidr)
	{
		throw new LogicException(__METHOD__.' not implemented in subclass of '.__CLASS__);
	}

	/**
	 * Gets the Global subnet mask for this IP Protocol
	 *
	 * @return IP_Address An IP Address representing the mask.
	 * @author Marcus Cobden
	 */
	public static function get_global_netmask()
	{
		throw new LogicException(__METHOD__.' not implemented in subclass of '.__CLASS__);
	}

	/**
	 * Creates an IP_Network_Address for the supplied string
	 *
	 * @param string $address IP Network Address string.
	 * @param string $cidr Optional CIDR number. If not supplied It is assumed to be part of the address string
	 * @return IP_Network_Address
	 */
	public static function factory($address, $cidr = NULL)
	{
		if ($address instanceof IP_Network_Address)
		{
			if ($cidr !== NULL AND $cidr !== $address->cidr)
			{
				$class = get_class($address);
				return new $class($address->address, $cidr);
			}
			return $address;
		}
		
		if ($cidr === NULL)
		{
			$parts = explode('/', $address, 2);

			if (count($parts) != 2)
				throw new InvalidArgumentException("Missing CIDR notation on '$address'.");

			list($address, $cidr) = $parts;
		}

		if (is_string($cidr))
		{
			if ( ! ctype_digit($cidr))
				throw new InvalidArgumentException("Malformed CIDR suffix '$cidr'.");

			$cidr = intval($cidr);
		}

		if ( ! $address instanceof IP_Address)
		{
			$address = IP_Address::factory($address);
		}

		if ($address instanceof IPv4_Address)
			return new IPv4_Network_Address($address, $cidr);
		elseif ($address instanceof IPv6_Address)
			return new IPv6_Network_Address($address, $cidr);
		else
			throw new InvalidArgumentException('Unsupported IP_Address type \''.get_class($address).'\'.');
	}

	/**
	 * Compare 2 IP Network Address objects.
	 *
	 * This method is a wrapper for the compare_to method and is useful in callback situations, e.g.
	 * usort($addresses, array('IP_Network_Address', 'compare'));
	 *
	 * @param IP_Address $a The left hand side of the comparison.
	 * @param IP_Address $b The right hand side of the comparison.
	 * @return int The result of the comparison.
	 */
	public static function compare(IP_Network_Address $a, IP_Network_Address $b)
	{
		return $a->compare_to($b);
	}

	/**
	 * Construct an IP_Network_Address.
	 *
	 * @param IPAddress $address The IP Address of the host
	 * @param string $cidr The CIDR size of the network
	 */
	protected function __construct(IP_Address $address, $cidr)
	{
		if ( ! is_int($cidr) OR $cidr < 0 OR $cidr > static::MAX_SUBNET)
			throw new InvalidArgumentException("Invalid CIDR '.$cidr'.Invalid type or out of range for class ".get_class($this).".");

		$this->address = $address;
		$this->cidr = $cidr;
	}

	public function get_address()
	{
		return $this->address;
	}

	public function get_cidr()
	{
		return $this->cidr;
	}

	/**
	 * Calculates the first address in this subnet.
	 *
	 * @return IP_Address
	 */
	public function get_network_start()
	{
		return $this->address->bitwise_and($this->get_subnet_mask());
	}

	/**
	 * Calculates the last address in this subnet.
	 *
	 * @return IP_Address
	 */
	public function get_network_end()
	{
		return $this->get_subnet_mask()->bitwise_not()->bitwise_or($this->address);
	}

	/**
	 * Calculates the number of address in this subnet.
	 *
	 * @return integer
	 */
	public function get_network_address_count()
	{
		return pow(2, static::MAX_SUBNET - $this->cidr);
	}

	public function get_address_in_network($offset, $from_start = NULL)
	{
		if (is_int($offset))
		{
			$positive = ($offset >= 0);
		}
		elseif ($offset instanceOf Math_BigInteger)
		{
			$positive = ($offset->compare(new Math_BigInteger(0)) >= 0);
		}
		if ($from_start === NULL)
		{
			$from_start = $positive;
		}
		else
		{
			$from_start = ($from_start == TRUE);
		}

		if ($from_start)
		{
			$point = $this->get_network_start();
		}
		else
		{
			$point = $this->get_network_end();
		}

		if ( ! $positive)
		{
			if (is_int($offset))
			{
				$offset = abs($offset);
			}
			elseif ($offset instanceOf Math_BigInteger)
			{
				$offset = $offset->abs();
			}
		}

		if ($positive AND $from_start)
			return $point->add($offset);
		else
			return $point->subtract($offset);
	}

	/**
	 * Checks whether this is a Network Identifier
	 *
	 * @return boolean
	 */
	public function is_network_identifier()
	{
		return $this->address->compare_to($this->get_network_start()) == 0;
	}

	/**
	 * Get the Network Identifier for the network this address is in.
	 *
	 * @return IP_Network_Address
	 */
	public function get_network_identifier()
	{
		$classname = get_class($this);
		return new $classname($this->get_network_start(), $this->cidr);
	}

	/**
	 * Get the subnet mask for this network
	 *
	 * @return IP_Address
	 */
	public function get_subnet_mask()
	{
		return static::generate_subnet_mask($this->cidr);
	}

	/**
	 * Calculates whether two subnets share any portion of their address space.
	 *
	 * @param IP_Address $other The other subnet to compare to.
	 * @return void
	 */
	public function shares_subnet_space(IP_Network_Address $other)
	{
		$this->check_types($other);

		$first = $this;

		if ($this->cidr > $other->cidr)
		{
			list($first, $other) = array($other, $first);
		}

		$first_start = $first->get_network_start();
		$other_start = $other->get_network_start();
		$first_end   = $first->get_network_end();
		$other_end   = $other->get_network_end();

		return
			($first->get_network_start()->compare_to($other->get_network_start()) <= 0)
			AND
			($first->get_network_end()  ->compare_to($other->get_network_end()  ) >= 0);
	}

	/**
	 * Checks whether this subnet encloses the supplied subnet.
	 *
	 * @param IP_Address $other Subnet to test against.
	 * @return boolean
	 */
	public function encloses_subnet(IP_Network_Address $other)
	{
		$this->check_types($other);

		if ($this->cidr > $other->cidr)
			return FALSE;

		return $this->shares_subnet_space($other);
	}

	/**
	 * Checks whether the supplied IP fits within this subnet.
	 *
	 * @param IP_Address $ip IP to test against.
	 * @return boolean
	 */
	public function encloses_address(IP_Address $ip)
	{
		$this->check_ip_version($ip);

		return
			($this->get_network_start()->compare_to($ip) <= 0)
			AND
			($this->get_network_end()     ->compare_to($ip) >= 0);
	}

	/**
	 * Check that this and the argument are of the same type.
	 *
	 * @param IP_Network_Address $other The object to check.
	 * @return void
	 * @throws InvalidArgumentException If they are not of the same type.
	 */
	protected function check_types($other)
	{
		if (get_class($this) != get_class($other))
			throw new InvalidArgumentException('Incompatible types.');
	}

	/**
	 * Check that this and the argument are of the same IP protocol version
	 *
	 * @param IP_Address $other
	 * @return void
	 * @throws InvalidArgumentException If they are not of the same type.
	 */
	protected function check_ip_version(IP_Address $other)
	{
		if ($other::IP_VERSION !== static::IP_VERSION)
			throw new InvalidArgumentException("Incompatible types ('".get_class($this)."' and '".get_class($other)."').");
	}

	/**
	 * Compare this instance to another IP_Network_Address
	 *
	 * @param IP_Network_Address $other The instance to compare to
	 * @return integer
	 */
	public function compare_to(IP_Network_Address $other)
	{
		$cmp = $this->address->compare_to($other->address);

		if ($cmp == 0)
		{
			$cmp = $this->cidr - $other->cidr;
		}

		return $cmp;
	}

	/**
	 * Provides a string representation of this object
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->address.'/'.$this->cidr;
	}

	/**
	 * Find a block of a given size within the smallest network address among the blocks given
	 * 
	 * @param array $blocks An array of network addresses to search in.
	 * @param integer $block_size The desired network block size
	 * @return array(IP_Network_Address found, IP_Network_Address within), or array(NULL, NULL) if none found.
	 */
	public static function get_block_in_smallest($blocks, $block_size)
	{
		$smallest = NULL;
		$smallest_cidr = 0;

		foreach ($blocks as $block)
		{
			$cidr = $block->get_cidr();
			if ($cidr == $block_size)
			{
				return array($block, $block);
			}
			elseif ($cidr > $block_size)
			{
				continue;
			}
			elseif ($cidr > $smallest_cidr)
			{
				$smallest = $block;
				$smallest_cidr = $block->get_cidr();
			}
		}

		if ($smallest)
			return array(static::factory($smallest, $block_size), $smallest);
		else
			return array(NULL, NULL);
	}

	/**
	 * Find the portion of this network address block that does not overlap with the given blocks.
	 *
	 * @param array $used An array of network addresses to exclude, each of type IP_Network_Address
	 * @return array
	 */
	public function excluding($excluding)
	{
		$candidates = array($this);
		foreach ($excluding as $exclude)
		{
			$stack = $candidates;
			$candidates = array();

			while ( ! empty($stack))
			{
				$candidate = array_shift($stack);

				// Null == ok, TRUE == split, FALSE == excluded
				$split = NULL;
				if ($candidate->shares_subnet_space($exclude))
				{
					$split = ($candidate->cidr < $exclude->cidr);
				}
				if ($split === TRUE)
				{
					$stack = array_merge($candidate->split(), $stack);
				}
				elseif ($split === NULL)
				{
					$candidates[] = $candidate;
				}
			}

			if (empty($candidates))
				break;
		}
		return $candidates;
	}

	/**
	 * Split the network address to create 2^n network addresses.
	 *
	 * @param int $times The number of times to split the network address
	 * @return array
	 */	
	public function split($times = 1)
	{
		if (0 == $times)
			return array($this);

		$new_cidr = $this->cidr + $times;
		$one = new Math_BigInteger(1);
		$offset = $one->bitwise_leftShift(static::MAX_SUBNET - $new_cidr);

		$out = array();
		$pos = $this->address;
		for ($i=0; $i < pow(2, $times); $i++)
		{
			$out[] = static::factory($pos, $new_cidr);
			$pos = $pos->add($offset);
		}

		return $out;
	}
}
