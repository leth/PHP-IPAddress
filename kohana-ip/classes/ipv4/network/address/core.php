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

class Ipv4_Network_Address_Core extends IP_Network_Address
{
	const ip_version = 4;
	const max_subnet = 32;

	public static function generate_subnet_mask($subnet)
	{
		return Ipv4_Address::factory(join('.', unpack('C*', pack('N', PHP_INT_MAX << (self::max_subnet - $subnet)))));
	}

	/**
	 * Gets the Global subnet mask for this IP Protocol
	 *
	 * @return IP_Address An IP Address representing the mask.
	 * @author Marcus Cobden
	 */
	public static function get_global_netmask()
	{
		return self::generate_subnet_mask(self::max_subnet);
	}

	/**
	 * Calculates the Network Address for this address (IPv4) or the first ip of the subnet (IPv6)
	 *
	 * @return Ipv4_Network_Address TODO
	 */
	function get_network_address()
	{
		return $this->get_network_start();
	}

	public function get_network_class()
	{
		if ($this->cidr > 24)
		{
			return '1/' . pow(2, $this->cidr - 24) . ' C';
		}
		else if ($this->cidr > 16)
		{
			return pow(2, 24 - $this->cidr). ' C';

		}
		else if ($this->cidr > 8)
		{
			return pow(2, 16 - $this->cidr). ' B';
		}
		else
		{
			return pow(2, 8 - $this->cidr) . ' A';
		}
	}

	/**
	 * Calculates the Broadcast Address for this address.
	 *
	 * @return Ipv4_Network_Address
	 */
	function get_broadcast_address() {
		return $this->get_network_end();
	}

	// TODO Check this
	// public function as_ipv6_network_address()
	// {
	// 	$address = $this->address->as_ipv6_address();
	// 	$cidr = (IPv6NetworkAddress::max_subnet - Ipv4_Network_Address::max_subnet) + $this->cidr;
	// 	return new IPv6NetworkAddress($address, $cidr);
	// }
}
