<?php

class IPv6NetworkAddress extends IPNetworkAddress
{
	const max_subnet = 128;
	
	public static function generateSubnetMask($subnet)
	{
		$result = unpack('H*', pack('N*',
			PHP_INT_MAX << min(32, max(0, 32  - $subnet)),
			PHP_INT_MAX << min(32, max(0, 64  - $subnet)),
			PHP_INT_MAX << min(32, max(0, 96  - $subnet)),
			PHP_INT_MAX << min(32, max(0, 128 - $subnet))));
		return new IPv6Address(join(':', str_split($result[1],4)));
	}
}
