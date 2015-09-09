<?php

class Kohana_Exception extends Kohana_Kohana_Exception {
	
	public static function handler($e)
	{
		if ($e instanceof Exception) {
			parent($e);
		} else {
			var_dump($e);
			parent(new Exception("Invalid type sent to handler(): '".get_class($e)."'"));
		}
	}
	
}
