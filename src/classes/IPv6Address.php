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

class IPv6Address extends IPAddress
{
	const ip_version = 6;
	
	public static function factory($address)
	{
		if (is_string($address))
		{
			if(!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
				throw new InvalidArgumentException("'$address' is not a valid IPv6 Address.");
		
			$address = IPv6Address::padV6AddressString($address);
			$address = pack("H*", str_replace(':', '', $address));
		}
		else if ($address instanceOf Math_BigInteger)
		{
			// Do nothing
		}
		else
		{
			throw new InvalidArgumentException("Unsupported argument type.");
		}
		
		return new IPv6Address($address);
	}
	
	protected function __construct($address)
	{
		if ($address instanceOf Math_BigInteger){
			parent::__construct(str_pad($address->abs()->toBytes(), 16, chr(0), STR_PAD_LEFT));
		}
		else
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
	 * Add the given address to this one.
	 *
	 * @param IPAddress $other The other operand.
	 * @return IPAddress An address representing the result of the operation.
	 */
	public function add(IPAddress $other)
	{
		$this->checkTypes($other);
		$left = new Math_BigInteger($this->address, 256);
		$right = new Math_BigInteger($other->address, 256);
		return new IPv6Address($left->add($right));
	}
	
	/**
	 * Subtract the given address from this one.
	 *
	 * @param IPAddress $other The other operand.
	 * @return IPAddress An address representing the result of the operation.
	 */
	public function subtract(IPAddress $other)
	{
		$this->checkTypes($other);
		$left = new Math_BigInteger($this->address, 256);
		$right = new Math_BigInteger($other->address, 256);
		return new IPv6Address($left->subtract($right));
	}
	
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
				break;
			case '|':
				$result = $other->address | $this->address;
				break;
			case '^':
				$result = $other->address ^ $this->address;
				break;
			case '~':
				$result = ~$this->address;
				break;
			
			default:
				throw new InvalidArgumentException('Unknown Operation.');
				break;
		}

		return new IPv6Address($result);
	}
	
	public function compareTo(IPAddress $other)
	{
		$this->checkTypes($other);
		
		if ($this->address < $other->address)
			return -1;
		elseif ($this->address > $other->address)
			return 1;
		else
			return 0;
	}
	
	public function __toString()
	{
		$tmp = unpack("H*", $this->address);
		return join(':', str_split($tmp[1], 4));
	}	
}
