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
namespace Leth\IPAddress\IP;
use \Leth\IPAddress\IP, \Leth\IPAddress\IPv4, \Leth\IPAddress\IPv6;
use ReturnTypeWillChange;

/**
 * An abstract representation of an IP Address.
 *
 * @author Marcus Cobden
 */
abstract class Address implements \ArrayAccess
{
	public const IP_VERSION  = -1;
	public const FORMAT_FULL    = 0;
	public const FORMAT_COMPACT = 1;

	/**
	 * Internal representation of the address. Format may vary.
	 * @var numeric
	 */
	protected $address;

	/**
	 * Create an IP address object from the supplied address.
	 *
	 * @param IP\Address|int|string|\Math_BigInteger $address The address to represent.
	 *
	 * @return \Leth\IPAddress\IP\Address|\Leth\IPAddress\IPv4\Address|\Leth\IPAddress\IPv6\Address An instance of a subclass of IP\Address; either IPv4\Address or IPv6\Address
	 */
	public static function factory(IP\Address|int|string|\Math_BigInteger $address): IP\Address|Ipv4\Address|IPv6\Address
	{
		if ($address instanceof self)
		{
			return $address;
		}
		elseif (is_int($address) || (is_string($address) && filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)))
		{
			return IPv4\Address::factory($address);
		}
		elseif ($address instanceof \Math_BigInteger || (is_string($address) && filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)))
		{
			return IPv6\Address::factory($address);
		}
		else
		{
			throw new \InvalidArgumentException('Unable to guess IP address type from \''.$address.'\'.');
		}
	}

	/**
	 * Compare 2 IP Address objects.
	 *
	 * This method is a wrapper for the compare_to method and is useful in callback situations, e.g.
	 * usort($addresses, array('IP\Address', 'compare'));
	 *
	 * @param IP\Address $a The left hand side of the comparison.
	 * @param IP\Address $b The right hand side of the comparison.
	 * @return int The result of the comparison.
	 */
	public static function compare(IP\Address $a, IP\Address $b): int
	{
		return $a->compare_to($b);
	}

	/**
	 * Create a new IP Address object.
	 *
	 * @param int|string $address The address to represent.
	 */
	protected function __construct(int|string $address)
	{
		$this->address = $address;
	}

	/**
	 * Add the given value to this address.
	 *
	 * @param integer|\Math_BigInteger $value
	 * @return IP\Address An address representing the result of the operation.
	 */
	abstract public function add($value): Address;

	/**
	 * Subtract the given value from this address.
	 *
	 * @param integer|\Math_BigInteger $value
	 * @return IP\Address An address representing the result of the operation.
	 */
	abstract public function subtract($value): Address;

	/**
	 * Compute the bitwise AND of this address and another.
	 *
	 * @param IP\Address $other The other operand.
	 * @return IP\Address An address representing the result of the operation.
	 */
	abstract public function bitwise_and(IP\Address $other);

	/**
	 * Compute the bitwise OR of this address and another.
	 *
	 * @param IP\Address $other The other operand.
	 * @return IP\Address An address representing the result of the operation.
	 */
	abstract public function bitwise_or(IP\Address $other): Address;

	/**
	 * Compute the bitwise XOR (Exclusive OR) of this address and another.
	 *
	 * @param IP\Address $other The other operand.
	 * @return IP\Address An address representing the result of the operation.
	 */
	abstract public function bitwise_xor(IP\Address $other): Address;

	/**
	 * Compute the bitwise NOT of this address.
	 *
	 * @return IP\Address An address representing the result of the operation.
	 */
	abstract public function bitwise_not(): Address;

	/**
	 * Compare this IP Address with another.
	 *
	 * @param IP\Address $other The instance to compare to.
	 * @return int The result of the comparison.
	 */
	abstract public function compare_to(IP\Address $other): int;

	/**
	 * Convert this object to a string representation
	 *
	 * @return string This IP address expressed as a string.
	 */
	public function __toString(): string
	{
		return $this->format(self::FORMAT_COMPACT);
	}

	/**
	 * Return the string representation of the address
	 *
	 * @return string This IP address expressed as a string.
	 */
	abstract public function format(int $mode): string;

	/**
	 * Check that this instance and the supplied instance are of the same class.
	 *
	 * @param IP\Address $other The object to check.
	 * @throws \InvalidArgumentException if objects are of the same class.
	 */
	protected function check_types(IP\Address $other): void
	{
		if (get_class($this) !== get_class($other)) {
			throw new \InvalidArgumentException('Incompatible types.');
		}
	}

	/**
	 * Get the specified octet from this address.
	 *
	 * @param integer $number
	 *
	 * @return ?integer An octet value the result of the operation.
	 */
	public function get_octet(int $number): ?int
	{
		$address = unpack("C*", $this->address);
		$index = (($number >= 0) ? $number : count($address) + $number);
		$index++;

		return $address[$index] ?? null;
	}

	/**
	 * Whether octet index in allowed range
	 *
	 * @param integer $offset
	 * @return boolean 
	 */
	public function offsetExists($offset): bool
	{
		return ($this->get_octet($offset) !== NULL);
	}

	/**
	 * Get the octet value from index
	 *
	 * @param integer $offset
	 *
	 * @return integer|null
	 */
	public function offsetGet($offset): ?int
	{
		return $this->get_octet($offset);
	}

	/**
	 * Operation unsupported
	 *
	 * @param integer $offset
	 * @param mixed $value
	 * @throws \LogicException
	 */
	#[ReturnTypeWillChange]
	public function offsetSet($offset, $value): mixed
	{
		throw new \LogicException('Operation unsupported');
	}

	/**
	 * Operation unsupported
	 *
	 * @param integer $offset
	 * @throws \LogicException
	 */
	#[ReturnTypeWillChange]
	public function offsetUnset($offset): void
	{
		throw new \LogicException('Operation unsupported');
	}
}
