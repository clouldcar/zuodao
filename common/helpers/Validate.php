<?php
namespace common\helpers;

class Validate {

	public static function isMobile($phone) 
	{
		if(!preg_match("/^1[34578]\d{9}$/", $phone))
		{
			return false;
		}

		return true;
	}
}