<?php

namespace WEEEOpen\Tarallo\Database;

abstract class DAO {
    protected $database;
    private $callback;

    public function __construct(Database $db, $callback) {
        $this->database = $db;
        $this->callback = $callback;
    }

	/**
	 * @return \PDO the PDO instance from Database
	 */
    protected function getPDO() {
        return call_user_func($this->callback);
    }

    protected static function multipleIn($prefix, $array) {
    	$in = '';
        foreach($array as $k => $v) {
            $in .= $prefix . $k . ', ';
        }
        return substr($in, 0, strlen($in) - 2); //remove last ', '
    }

	protected static function isArrayAndFull($something) {
		if(is_array($something) && !empty($something)) {
			return true;
		} else {
			return false;
		}
	}
}