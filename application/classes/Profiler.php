<?php

class Profiler extends Kohana_Profiler {
	
	public static function group_breakdown($group) {
		return array_map(function($tokens) {
			return Profiler::stats($tok);
		}, Profiler::groups()[$group]);
	}

}
