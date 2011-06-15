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

/**
 * An abstract representation of an IP Address.
 *
 * @author Marcus Cobden
 */
abstract class Ip_Address_Core
{
	const ip_version = -1;

	protected $_db;
	/**
	 * Internal representation of the address. Format may vary.
	 * @var mixed
	 */
	protected $address;

	/**
	 * Create an IP address object from the supplied address.
	 *
	 * @param string $address The address to represent.
	 * @return Ip_Address An instance of a subclass of Ip_Address either Ipv4_Address or Ipv6_Address
	 */
	public static function factory($address)
	{
		if (is_int($address) OR is_string($address) AND strpos($address, '.') !== FALSE)
			return Ipv4_Address::factory($address);
		elseif ($address instanceof Math_BigInteger OR strpos($address, ':') !== FALSE)
			return Ipv6_Address::factory($address);
		else
			throw new InvalidArgumentException('Unable to guess IP address type from \''.$address.'\'.');
	}

	public function db(Database $db)
	{
		$this->_db = $db;
	}
	/**
	 * Compare 2 IP Address objects.
	 *
	 * This method is a wrapper for the compareTo method and is useful in callback situations, e.g.
	 * usort($addresses, array('IPAddress', 'compare'));
	 *
	 * @param Ip_Address $a The left hand side of the comparison.
	 * @param Ip_Address $b The right hand side of the comparison.
	 * @return int The result of the comparison.
	 */
	public static function compare(Ip_Address $a, Ip_Address $b)
	{
		return $a->compare_to($b);
	}

	/**
	 * Create a new IP Address object.
	 *
	 * @param string $address The address to represent.
	 */
	protected function __construct($address)
	{
		$this->address = $address;
	}

	/**
	 * Add the given address to this one.
	 *
	 * @param Ip_Address $other The other operand.
	 * @return Ip_Address An address representing the result of the operation.
	 */
	public abstract function add(Ip_Address $other);

	/**
	 * Subtract the given address from this one.
	 *
	 * @param Ip_Address $other The other operand.
	 * @return Ip_Address An address representing the result of the operation.
	 */
	public abstract function subtract(Ip_Address $other);

	/**
	 * Compute the bitwise AND of this address and another.
	 *
	 * @param Ip_Address $other The other operand.
	 * @return Ip_Address An address representing the result of the operation.
	 */
	public abstract function bitwise_and(Ip_Address $other);

	/**
	 * Compute the bitwise OR of this address and another.
	 *
	 * @param Ip_Address $other The other operand.
	 * @return Ip_Address An address representing the result of the operation.
	 */
	public abstract function bitwise_or(Ip_Address $other);

	/**
	 * Compute the bitwise XOR (Exclusive OR) of this address and another.
	 *
	 * @param IPAddress $other The other operand.
	 * @return IPAddress An address representing the result of the operation.
	 */
	public abstract function bitwise_xor(Ip_Address $other);

	/**
	 * Compute the bitwise NOT of this address.
	 *
	 * @return IPAddress An address representing the result of the operation.
	 */
	public abstract function bitwise_not();

	/**
	 * Compare this IP Address with another.
	 *
	 * @param IPAddress $other The instance to compare to.
	 * @return int The result of the comparison.
	 */
	public abstract function compare_to(Ip_Address $other);

	/**
	 * Convert this object to a string representation for inserting into Database
	 *
	 * @return string This IP address expressed as a quoted string.
	 */
	public function __toString()
	{
		if(isset($this->_db))
			return $this->_db->quote($this->address());
		else
			return $this->address();
	}

	/**
	 * Return the string representation of the address
	 *
	 * @return string This IP address expressed as a string.
	 */
	public abstract function address();

	/**
	 * Check that this instance and the supplied instance are of the same class.
	 *
	 * @param Ip_Address $other The object to check.
	 * @throws Kohana_Exception if objects are of the same class.
	 */
	protected function check_types(Ip_Address $other)
	{
		if (get_class($this) != get_class($other))
			throw new Kohana_Exception('Incompatible types.');
	}
}
