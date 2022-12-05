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
use \Leth\IPAddress\IPv6;

class NetworkAddress extends \Leth\IPAddress\IP\NetworkAddress
{
	public const IP_VERSION = 6;
	public const MAX_SUBNET = 128;

	public static function generate_subnet_mask(int $cidr): IPv6\Address
	{
		$masks = array();
		for ($i=1; $i <= 4; $i++)
		{
			// left shift operates over arch-specific integer sizes,
			// so we have to special case 32 bit shifts
			$shift = min(32, max(0, 32*$i  - $cidr));
			if ($shift === 32)
			{
				$masks[] = 0;
			}
			else
			{
				$masks[] = (~0) << $shift;
			}
		}
		$result = unpack('H*', pack('N4', $masks[0], $masks[1], $masks[2], $masks[3]));
		return IPv6\Address::factory(implode(':', str_split($result[1], 4)));
	}

	/**
	 * Gets the Global subnet mask for this IP Protocol
	 *
	 * @return Address An IP Address representing the mask.
	 * @author Marcus Cobden
	 */
	public static function get_global_netmask(): Address
	{
		return static::generate_subnet_mask(static::MAX_SUBNET);
	}
}
