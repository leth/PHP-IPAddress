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
namespace Leth\IPAddress\IP;
use \Leth\IPAddress\IP, \Leth\IPAddress\IPv4, \Leth\IPAddress\IPv6, \Traversable;

/**
 * An abstract representation of an IP Address in a given network
 *
 * @package default
 * @author Marcus Cobden
 */
abstract class NetworkAddress implements \IteratorAggregate, \Countable
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
	 * @return IP\Address An IP address representing the mask.
	 */
	public static function generate_subnet_mask($cidr)
	{
		throw new \LogicException(__METHOD__.' not implemented in subclass of '.__CLASS__);
	}

	/**
	 * Gets the Global subnet mask for this IP Protocol
	 *
	 * @return IP\Address An IP Address representing the mask.
	 * @author Marcus Cobden
	 */
	public static function get_global_netmask()
	{
		throw new \LogicException(__METHOD__.' not implemented in subclass of '.__CLASS__);
	}

	/**
	 * Creates an IP\NetworkAddress for the supplied string
	 *
	 * @param string $address IP Network Address string.
	 * @param string $cidr Optional CIDR number. If not supplied It is assumed to be part of the address string
	 * @return IP\NetworkAddress
	 */
	public static function factory($address, $cidr = NULL)
	{
		if ($address instanceof IP\NetworkAddress)
		{
			if ($cidr !== NULL AND $cidr !== $address->cidr)
			{
				$class = get_class($address);
				return new $class($address->address, $cidr);
			}
			return $address;
		}

		$parts = explode('/', $address, 2);
		if (count($parts) == 2) {
			if ($cidr == NULL)
				// Parse CIDR from $address variable because $cidr is null
				list($address, $cidr) = $parts;
			else
				// Ignore CIDR into $address variable
				list($address) = $parts;
		}

		if (is_string($cidr))
		{
			if ( ! ctype_digit($cidr))
				throw new \InvalidArgumentException("Malformed CIDR suffix '$cidr'.");

			$cidr = intval($cidr);
		}

		if ( ! $address instanceof IP\Address)
		{
			$address = IP\Address::factory($address);
		}

		if ($address instanceof IPv4\Address)
			return new IPv4\NetworkAddress($address, $cidr);
		elseif ($address instanceof IPv6\Address)
			return new IPv6\NetworkAddress($address, $cidr);
		else
			throw new \InvalidArgumentException('Unsupported IP Address type \''.get_class($address).'\'.');
	}

	/**
	 * Compare 2 IP Network Address objects.
	 *
	 * This method is a wrapper for the compare_to method and is useful in callback situations, e.g.
	 * usort($addresses, array('IP\NetworkAddress', 'compare'));
	 *
	 * @param IP\Address $a The left hand side of the comparison.
	 * @param IP\Address $b The right hand side of the comparison.
	 * @return int The result of the comparison.
	 */
	public static function compare(IP\NetworkAddress $a, IP\NetworkAddress $b)
	{
		return $a->compare_to($b);
	}

	/**
	 * Merge adjacent network blocks
	 *
	 * Ajacent blocks can only be merged if they belong to the same parent block
	 *
	 * @param array NetworkAddresses to merge
	 * @return array NetworkAddresses remaining after merging
	 */
	public static function merge(array $network_addresses)
	{
		$net_addr_index = array();
		foreach ($network_addresses as $net_addr) {
			// Ensure sure we're only dealing with network identifiers
			$net_addr = $net_addr->get_network_identifier();
			$net_addr_index[$net_addr::IP_VERSION][$net_addr->cidr][] = $net_addr;
		}
		// We're done with this structure now
		unset($network_addresses);

		$out = array();
		foreach ($net_addr_index as $version => $cidr_addrs)
		{
			$max = $version == 4 ? IPv4\NetworkAddress::MAX_SUBNET : IPv6\NetworkAddress::MAX_SUBNET;
			// smallest networks first (largest cidr)
			// We have to loop by index because we modify the array while we iterate
			for ($cidr = $max; $cidr > 0; $cidr--)
			{
				if (! array_key_exists($cidr, $cidr_addrs))
					continue;
				$net_addrs = $cidr_addrs[$cidr];
				if (count($net_addrs) == 1)
				{
					$out[] = $net_addrs[0];
					continue;
				}

				usort($net_addrs, array('Leth\IPAddress\IP\NetworkAddress', 'compare'));

				$last_added = NULL;
				for ($i = 0; $i < count($net_addrs) - 1; $i++) {
					$a = $net_addrs[$i];
					$b = $net_addrs[$i + 1];
					if ($a->compare_to($b) === 0)
						continue;
					$parent = $a->get_parent();
					if ($parent->compare_to($b->get_parent()) === 0)
					{
						$cidr_addrs[$parent->cidr][] = $parent;
						$last_added = $b;
					}
					elseif($a !== $last_added)
					{
						$out[] = $a;
						$last_added = $a;
					}
				}
				if ($last_added === NULL || ($last_added !== $b && $last_added->compare_to($b) !== 0))
					$out[] = $b;
				// We're done with these, remove them to allow GC
				unset($cidr_addrs[$cidr]);
			}
		}
		return $out;
	}

	/**
	 * Construct an IP\NetworkAddress.
	 *
	 * @param IPAddress $address The IP Address of the host
	 * @param string $cidr The CIDR size of the network
	 */
	protected function __construct(IP\Address $address, $cidr)
	{
		// Default CIDR equal single host
		if ($cidr === NULL) {
			$cidr = static::MAX_SUBNET;
		}
		if ( ! is_int($cidr) OR $cidr < 0 OR $cidr > static::MAX_SUBNET)
			throw new \InvalidArgumentException("Invalid CIDR '.$cidr'.Invalid type or out of range for class ".get_class($this).".");

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
	 * Get the NetworkAddress immediately enclosing this one
	 *
	 * @return IP\NetworkAddress
	 */
	public function get_parent()
	{
		if ($this->cidr == 0)
			return NULL;
		$parent_cidr = $this->cidr - 1;
		$parent_addr = $this->address->bitwise_and(static::generate_subnet_mask($parent_cidr));
		return static::factory($parent_addr, $parent_cidr);
	}

	/**
	 * Calculates the first address in this subnet.
	 *
	 * @return IP\Address
	 */
	public function get_network_start()
	{
		return $this->address->bitwise_and($this->get_subnet_mask());
	}

	/**
	 * Calculates the last address in this subnet.
	 *
	 * @return IP\Address
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
	public function get_NetworkAddress_count()
	{
		return pow(2, static::MAX_SUBNET - $this->cidr);
	}

	public function get_address_in_network($offset, $from_start = NULL)
	{
		if (is_int($offset))
		{
			$positive = ($offset >= 0);
		}
		elseif ($offset instanceOf \Math_BigInteger)
		{
			$positive = ($offset->compare(new \Math_BigInteger(0)) >= 0);
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
			elseif ($offset instanceOf \Math_BigInteger)
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
	 * @return IP\NetworkAddress
	 */
	public function get_network_identifier()
	{
		$classname = get_class($this);
		return new $classname($this->get_network_start(), $this->cidr);
	}

	/**
	 * Get the subnet mask for this network
	 *
	 * @return IP\Address
	 */
	public function get_subnet_mask()
	{
		return static::generate_subnet_mask($this->cidr);
	}

	/**
	 * Calculates whether two subnets share any portion of their address space.
	 *
	 * @param IP\Address $other The other subnet to compare to.
	 * @return void
	 */
	public function shares_subnet_space(IP\NetworkAddress $other)
	{
		$this->check_types($other);

		$first = $this;

		if ($this->cidr > $other->cidr)
		{
			list($first, $other) = array($other, $first);
		}

		return
			($first->get_network_start()->compare_to($other->get_network_start()) <= 0)
			AND
			($first->get_network_end()  ->compare_to($other->get_network_end()  ) >= 0);
	}

	/**
	 * Checks whether this subnet encloses the supplied subnet.
	 *
	 * @param IP\Address $other Subnet to test against.
	 * @return boolean
	 */
	public function encloses_subnet(IP\NetworkAddress $other)
	{
		$this->check_types($other);

		if ($this->cidr > $other->cidr)
			return FALSE;

		return $this->shares_subnet_space($other);
	}

	/**
	 * Checks whether the supplied IP fits within this subnet.
	 *
	 * @param IP\Address $ip IP to test against.
	 * @return boolean
	 */
	public function encloses_address(IP\Address $ip)
	{
		$this->check_IP_version($ip);

		return
			($this->get_network_start()->compare_to($ip) <= 0)
			AND
			($this->get_network_end()     ->compare_to($ip) >= 0);
	}

	/**
	 * Check that this and the argument are of the same type.
	 *
	 * @param IP\NetworkAddress $other The object to check.
	 * @return void
	 * @throws \InvalidArgumentException If they are not of the same type.
	 */
	protected function check_types($other)
	{
		if (get_class($this) != get_class($other))
			throw new \InvalidArgumentException('Incompatible types.');
	}

	/**
	 * Check that this and the argument are of the same IP protocol version
	 *
	 * @param IP\Address $other
	 * @return void
	 * @throws \InvalidArgumentException If they are not of the same type.
	 */
	protected function check_IP_version(IP\Address $other)
	{
		if ($other::IP_VERSION !== static::IP_VERSION)
			throw new \InvalidArgumentException("Incompatible types ('".get_class($this)."' and '".get_class($other)."').");
	}

	/**
	 * Compare this instance to another IP\NetworkAddress
	 *
	 * @param IP\NetworkAddress $other The instance to compare to
	 * @return integer
	 */
	public function compare_to(IP\NetworkAddress $other)
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
	 * @return array(IP\NetworkAddress found, IP\NetworkAddress within), or array(NULL, NULL) if none found.
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
	 * @param array $used An array of network addresses to exclude, each of type IP\NetworkAddress
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
		$shift = static::MAX_SUBNET - $new_cidr;
		if ($shift < 0)
			throw new \InvalidArgumentException('Cannot split beyond smallest subnet size');

		$one = new \Math_BigInteger(1);
		$offset = $one->bitwise_leftShift($shift);

		$out = array();
		$pos = $this->address;
		for ($i=0; $i < pow(2, $times); $i++)
		{
			$out[] = static::factory($pos, $new_cidr);
			$pos = $pos->add($offset);
		}

		return $out;
	}

	/**
	 * Get iterator for this network
	 * Implement \IteratorAggregate
	 *
	 * @return Traversable
	 */
	public function getIterator(): Traversable
	{
		return new NetworkAddressIterator($this);
	}

	/**
	 * Get array of addresses in this network
	 *
	 * Warning: May use a lot of memory if used with large networks.
	 *          Consider using an iterator and the count() method instead.
	 * @return array
	 */
	public function toArray()
	{
		return iterator_to_array($this, false);
	}

	/**
	 * Get count addresses in this network
	 * Implement \Countable
	 *
	 * @return integer
	 */
	public function count(): int
	{
		return $this->get_NetworkAddress_count();
	}
}
