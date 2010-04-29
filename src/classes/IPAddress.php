<?php
/*
 * This file is part of the PHP-IPAddress library.
 *
 * The PHP-IPAddress library is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * The PHP-IPAddress library is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
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
abstract class IPAddress
{
	/**
	 * Internal representation of the address. Format may vary.
	 * @var mixed
	 */
	protected $address;
	
	/**
	 * Create an IP address object from the supplied address.
	 *
	 * @param string $address The address to represent.
	 * @return IPAddress An instance of a subclass of IPAddress either IPv4Address or IPv6Address
	 */
	public static function factory($address)
	{
		if (strpos($address, '.') !== FALSE)
			return new IPv4Address($address);
		else if (strpos($address, ':') !== FALSE)
			return new IPv6Address($address);
		else
			throw new InvalidArgumentException("Unable to guess IP address type from '$address'.");
	}
	
	/**
	 * Compare 2 IP Address objects.
	 * 
	 * This method is a wrapper for the compareTo method and is useful in callback situations, e.g.
	 * usort($addresses, array('IPAddress', 'compare'));
	 *
	 * @param IPAddress $a The left hand side of the comparison.
	 * @param IPAddress $b The right hand side of the comparison.
	 * @return int The result of the comparison.
	 */
	public static function compare(IPAddress $a, IPAddress $b)
	{
		return $a->compareTo($b);
	}
	
	/**
	 * Create a new IP Address object.
	 *
	 * @param string $address The address to represent.
	 */
	public function __construct($address)
	{
		$this->address = $address;
	}
	
	/**
	 * Compute the bitwise AND of this address and another.
	 *
	 * @param IPAddress $other The other operand.
	 * @return IPAddress An address representing the result of the operation.
	 */
	public abstract function bitwiseAND(IPAddress $other);

	/**
	 * Compute the bitwise OR of this address and another.
	 *
	 * @param IPAddress $other The other operand.
	 * @return IPAddress An address representing the result of the operation.
	 */
	public abstract function bitwiseOR(IPAddress $other);

	/**
	 * Compute the bitwise XOR (Exclusive OR) of this address and another.
	 *
	 * @param IPAddress $other The other operand.
	 * @return IPAddress An address representing the result of the operation.
	 */
	public abstract function bitwiseXOR(IPAddress $other);

	/**
	 * Compute the bitwise NOT of this address.
	 *
	 * @return IPAddress An address representing the result of the operation.
	 */
	public abstract function bitwiseNOT();

	/**
	 * Compare this IP Address with another.
	 * 
	 * @param IPAddress $other The instance to compare to.
	 * @return int The result of the comparison.
	 */
	public abstract function compareTo(IPAddress $other);
	
	/**
	 * Convert this object to a string representation
	 *
	 * @return string This IP address expressed as a string.
	 */
	public abstract function __toString();
	
	/**
	 * Check that this instance and the supplied instance are of the same class.
	 *
	 * @param IPAddress $other The object to check.
	 * @return boolean True if objects are of the same class.
	 */
	protected function checkTypes(IPAddress $other)
	{
		if (get_class($this) != get_class($other))
			throw new Exception('Incompatible types.');
	}
}
