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
namespace Leth\IPAddress\IP\Impl;
use \Leth\IPAddress\IP;

class NetworkAddressIterator implements \Iterator
{
	/**
	 * The network of iterator
	 *
	 * @var IP\NetworkAddress
	 */
	protected $network;

	/**
	 * The position of iterator
	 *
	 * @var IP\Address
	 */
	protected $position;

	public function __construct(NetworkAddress $network)
	{
		$this->network = $network;
		$this->rewind();
	}

	/**
	 * Set the pointer of iterator to a first network address
	 * Implement \Iterator
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->position = $this->network->get_network_start();
	}

	/**
	 * Get the value from iterator
	 * Implement \Iterator
	 *
	 * @return IP\Address
	 */
	public function current()
	{
		return $this->position;
	}

	/**
	 * Get the key from iterator
	 * Implement \Iterator
	 *
	 * @return string
	 */
	public function key()
	{
		return $this->position->__toString();
	}

	/**
	 * Move the pointer of iterator to a next network address
	 * Implement \Iterator
	 *
	 * @return void
	 */
	public function next()
	{
		$this->position = $this->position->add(1);
	}

	/**
	 * Next network address is valid
	 * Implement \Iterator
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return ($this->position->compare_to($this->network->get_network_end()) <= 0);
	}
}
