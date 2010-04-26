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
	
	public function getNetworkClass()
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
