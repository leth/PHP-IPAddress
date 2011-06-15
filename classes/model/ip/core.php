<?php defined('SYSPATH') or die('No direct script access.');
class Model_IP_Core extends ORM
{
	public function __set($column, $value)
	{
		switch ($column)
		{
			case 'address':
				$value = Ip_Address::factory($value);
				break;
		}
		parent::__set($column, $value);
	}
}