<?php

class Time_Unit {
	
	private static function rel($n, $unit) {
		return strtotime("+{$n} {$unit}") - time();
	}

	public static function seconds($n) {
		return self::rel($n, __FUNCTION__);
	}
	
	public static function minutes($n) {
		return self::rel($n, __FUNCTION__);
	}

	public static function hours($n) {
		return self::rel($n, __FUNCTION__);
	}
	
	public static function days($n) {
		return self::rel($n, __FUNCTION__);
	}
	
	public static function weeks($n) {
		return self::rel($n, __FUNCTION__);
	}
	
	public static function months($n) {
		return self::rel($n, __FUNCTION__);
	}
	
	public static function years($n) {
		return self::rel($n, __FUNCTION__);
	}
}