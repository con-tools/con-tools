<?php

class Database_MySQLi extends Kohana_Database_MySQLi {
	
	public function connect() {
		$tries = 3;
		while (true) {
			try {
				return parent::connect();
			} catch (Database_Exception $e) {
				if (--$tries < 0)
					throw $e;
				Logger::warn("Database connection failed: " . $e->getMessage() . ", retrying");
			}
		}
	}
}
