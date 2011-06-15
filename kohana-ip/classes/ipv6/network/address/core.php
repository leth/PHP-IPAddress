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

class Ipv6_Network_Address_Core extends Ip_Network_Address
{
	const ip_version = 6;
	const max_subnet = 128;

	public static function generate_subnet_mask($subnet)
	{
		$result = unpack('H*', pack('N*',
			PHP_INT_MAX << min(32, max(0, 32  - $subnet)),
			PHP_INT_MAX << min(32, max(0, 64  - $subnet)),
			PHP_INT_MAX << min(32, max(0, 96  - $subnet)),
			PHP_INT_MAX << min(32, max(0, 128 - $subnet))));
		return Ipv6_Address::factory(join(':', str_split($result[1], 4)));
	}

	/**
	 * Gets the Global subnet mask for this IP Protocol
	 *
	 * @return Ip_Address An IP Address representing the mask.
	 * @author Marcus Cobden
	 */
	public static function get_global_netmask()
	{
		return self::generate_subnet_mask(self::max_subnet);
	}
}
