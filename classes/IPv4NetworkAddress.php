<?php

class IPv4NetworkAddress extends IPNetworkAddress
{
	const max_subnet = 32;

	public static function generateSubnetMask($subnet)
	{
		return new IPv4Address(join('.', unpack('C*', pack('N', PHP_INT_MAX << (self::max_subnet - $subnet)))));
	}
	
	/**
	 * Calculates the Network Address for this address (IPv4) or the first ip of the subnet (IPv6)
	 *
	 * @return IPv4NetworkAddress TODO
	 */
	function getNetworkAddress() 
	{
		return new IPv4NetworkAddress($this->getNetworkStart(), $this->cidr);
	}
	
	/**
	 * Calculates the Broadcast Address for this address.
	 * 
	 * @return IPv4NetworkAddress TODO
	 */
	function getBroadcastAddress() {
		return new IPv4NetworkAddress($this->getNetworkEnd(), $this->cidr);
	}
	
	public function asIPv6NetworkAddress()
	{
		$address = $this->address->asIPv6Address();
		$cidr = (IPv6NetworkAddress::max_subnet - self::max_subnet) + $this->cidr;
		return new IPv6NetworkAddress($address, $cidr);
	}
}
