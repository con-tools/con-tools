<?php

class Kohana_Exception extends Kohana_Kohana_Exception {
	
	public static function handler($e)
	{
		if ($e instanceof Exception) {
			return Kohana_Kohana_Exception::handler($e);
		} else {
			var_dump($e);
			return Kohana_Kohana_Exception::handler(new Exception("Invalid type sent to handler(): '".get_class($e)."'"));
		}
	}
	
}
