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
namespace Leth\IPAddress\IPv4\Impl;
use \Leth\IPAddress\IPv4;

class NetworkAddress extends \Leth\IPAddress\IP\NetworkAddress
{
	const IP_VERSION = 4;
	const MAX_SUBNET = 32;

	public static function generate_subnet_mask($subnet)
	{
		$mask = 0;
		// left shift operates over arch-specific integer sizes,
		// so we have to special case 32 bit shifts
		if ($subnet > 0)
		{
			$mask  = (~$mask) << (static::MAX_SUBNET - $subnet);
		}
		
		return IPv4\Address::factory(implode('.', unpack('C4', pack('N', $mask))));
	}

	/**
	 * Gets the Global subnet mask for this IP Protocol
	 *
	 * @return IP\Address An IP Address representing the mask.
	 * @author Marcus Cobden
	 */
	public static function get_global_netmask()
	{
		return static::generate_subnet_mask(static::MAX_SUBNET);
	}

	/**
	 * Calculates the Network Address for this address (IPv4) or the first ip of the subnet (IPv6)
	 *
	 * @return IPv4\NetworkAddress TODO
	 */
	public function get_NetworkAddress()
	{
		return $this->get_network_start();
	}

	public function get_network_class()
	{
		if ($this->cidr > 24)
		{
			return '1/'.pow(2, $this->cidr - 24).' C';
		}
		elseif ($this->cidr > 16)
		{
			return pow(2, 24 - $this->cidr).' C';

		}
		elseif ($this->cidr > 8)
		{
			return pow(2, 16 - $this->cidr).' B';
		}
		else
		{
			return pow(2, 8 - $this->cidr).' A';
		}
	}

	/**
	 * Calculates the Broadcast Address for this address.
	 *
	 * @return IPv4\NetworkAddress
	 */
	public function get_broadcast_address()
	{
		return $this->get_network_end();
	}

	// TODO Check this
	// public function as_IPv6\NetworkAddress()
	// {
	// 	$address = $this->address->as_IPv6\address();
	// 	$cidr = (IPv6\NetworkAddress::MAX_SUBNET - IPv4\NetworkAddress::MAX_SUBNET) + $this->cidr;
	// 	return new IPv6\NetworkAddress($address, $cidr);
	// }
}
