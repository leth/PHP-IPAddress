<?php

class IPv6Address extends IPAddress
{
	
	public static function factory($address)
	{
		return new IPv6Address($address);
	}
	
	function __construct($address)
	{
		if(!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			throw new Exception("'$address' is not a valid IPv6 Address");
		
		$address = IPv6Address::padV6AddressString($address);
		
		$address = pack("H*", str_replace(':', '', $address));
		
		parent::__construct($address);
	}
	
	/**
	 * This makes an IPv6 address fully qualified. It replaces :: with appropriate 0000 blocks, and 
	 * pads out all dropped 0s
	 * 
	 * IE: 2001:630:d0:: becomes 2001:0630:00d0:0000:0000:0000:0000:0000
	 *
	 * @param string $address IPv6 address to be padded
	 * @return string A fully padded string ipv6 address
	 */
	public static function padV6AddressString($address)
	{
		$ipparts = explode("::",$address, 2);
		$head = $ipparts[0];
		$tail = $ipparts[1];

		$headparts = explode(":",$head);
		foreach($headparts as $val)
			$ippad[] = str_pad($val,4,"0",STR_PAD_LEFT);
		
		if(count($ipparts) > 1) {
			$tailparts = explode(":", $tail);
			$midparts = 8 - count($headparts) - count($tailparts);

			for($i=0; $i < $midparts; $i++)
				$ippad[]="0000";

			foreach($tailparts as $val)
				$ippad[] = str_pad($val,4,"0",STR_PAD_LEFT);
		}

		return join(':', $ippad);
	}

	// TODO fix this.
	// public function isEncodedIPv4Address()
	// {
	// 	$address = (string) $this;
	// 	return preg_match("/^0000:0000:0000:ffff:(0\d{1,3}\.0\d{1,3}\.0\d{1,3}\.0\d{1,3})$/","\\1", $address) != 0;
	// }
	// 
	// public function asIPv4Address()
	// {
	// 	$address = (string) $this;
	// 	$match_count = preg_match("/^0000:0000:0000:ffff:(0\d{1,3}\.0\d{1,3}\.0\d{1,3}\.0\d{1,3})$/","\\1", $address, $matches);
	// 	
	// 	if ($match_count == 0)
	// 		throw new Exception("Not an IPv4 Address encoded in an IPv6 Address");
	// 	
	// 	$address = join('.', array_map('intval', explode(':', $matches[1])));
	// 	
	// 	return new IPv4Address();
	// }
	
	/**
	  * Calculates the Bitwise & (AND) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwiseAND(IPAddress $other)
	{
		return $this->bitwiseOperation('&', $other);
	}
	
	/**
	  * Calculates the Bitwise | (OR) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwiseOR(IPAddress $other)
	{
		return $this->bitwiseOperation('|', $other);
	}
	
	/**
	  * Calculates the Bitwise ^ (XOR) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwiseXOR(IPAddress $other)
	{
		return $this->bitwiseOperation('^', $other);
	}
	
	/**
	  * Calculates the Bitwise ~ (NOT) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwiseNOT()
	{
		return $this->bitwiseOperation('~');
	}
	
	public function bitwiseOperation($operation, $other = NULL)
	{
		if ($operation != '~')
			$this->checkTypes($other);
		
		switch ($operation) {
			case '&':
				$result = $other->address & $this->address;
				$bits = min($args[0]->subnet, $args[1]->subnet);
				break;
			case '|':
				$reult = $other->address | $this->address;
				$bits = min($args[0]->subnet, $args[1]->subnet);
				break;
			case '^':
				$result = $other->address ^ $this->address;
				$bits = min($args[0]->subnet, $args[1]->subnet);
				break;
			case '~':
				$result = ~$this->address;
				$bits = $args[0]->subnet;
				break;
			
			default:
				throw new Exception('Unknown Operation.');
				break;
		}
		
		$res = join(':', str_split(unpack("H*", $result, 4)));

		return new IPv6Address($res);
	}
	
	public function compareTo(IPAddress $other)
	{
		$this->checkTypes($other);
		
		// TODO can this be done with binary data?
		$ip1 = str_replace(":", "", (string) $this);
		$ip2 = str_replace(":", "", (string) $other);

		$ip1 = str_split($ip1);
		$ip2 = str_split($ip2);
		foreach($ip1 as $idx=>$val) {
			if($val < $ip2[$idx]) return -1;
			else if($val > $ip2[$idx]) return 1;
		}
		return 0;
	}
	
	public function __toString()
	{
		$tmp = unpack("H*", $this->address);
		return join(':', str_split($tmp[1], 4));
	}
}