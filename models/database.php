<?php

class Database {

	public static $singleton;
	public $connection;

	/** Return the single database connection */
	public static function getConnection() {
		if(empty(self::$singleton)) {
			$f3=Base::instance();
			extract($f3->get('db'));
			self::$singleton=new DB\SQL(
					'mysql:host='.$server.';port=3306;dbname='.$name,
					$username,
					$password
					);
		}
		return self::$singleton;
	}

	/** Create a new database object */
	public function __construct() {
		$this->connection = self::getConnection();
	}

	/** Perform a direct database query */
	public function query($sql, $params=NULL) {
		if($params != NULL) {
			return $this->connection->exec($sql, $params);
		}
		return $this->connection->exec($sql);
	}

}

?>
