<?php
class Database
{
	private $server;
	private $database;
	private $username;
	private $password;

	public $link;

	public function database($server, $database, $username, $password) {
		$this->server = $server;
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
	}

	/*
	 * Connects to the database. It's called from query and the various other
	 * database functions so it only connects when it really needs to.
	 */
	public function connect() {
		if(!is_resource($this->link)) {
			$this->link = mysql_connect($this->server, $this->username, $this->password) or die(mysql_error());
			return mysql_select_db($this->database, $this->link) or die(mysql_error());
		} else return true;
	}

	/**
	 * These are the functions you'll use from your own scripts.
	 *
	 * Warning: These functions don't do any escaping! Please
	 * escape data before it gets to these functions.
	 *
	 * You can use the included escape() to escape singular
	 * strings or single-dimensional arrays of them.
	 */

	public function get($what, $from, $where = '', $arraytype = MYSQL_ASSOC) {
		$sql = "SELECT $what FROM $from";
		if(!empty($where)) {
			if(is_array($where)) {
				$where = $this->arrayToWhereSQL($where);
			}

			$sql .= ' WHERE '.$where;
		}

		$result = $this->query($sql);

		while ($row = $this->fetch($result, $arraytype)) {
			$rows[] = $row;
		}

		return isset($rows) ? $rows : false;
	}

	public function insert($data, $table) {
		list($columns, $values) = $this->arrayToInsertSQL($data);	

		$sql = "INSERT INTO $table ($columns) VALUES ($values)";

		if($this->query($sql))
			return mysql_insert_id();
		else 
			return false;
	}

	public function update($what, $where, $with) {
		$where = $this->arrayToWhereSQL($where);
		$with = $this->arrayToWhereSQL($with);
		$sql = "UPDATE $what SET $with WHERE $where";
		return $this->query($sql, $this->link);
	}

	public function delete($where, $table) {
		$where = $this->arrayToWhereSQL($where);
		$sql = "DELETE FROM $table WHERE $where";
		return $this->query($sql, $this->link);
	}

	/**
	 * Helper functions
	 */

	protected function arrayToInsertSQL($data) {
		if(!is_array(reset($data)))
			$data = array($data);

		$columns = array_keys((array) reset($data));
		$columns = implode(', ', $columns);

		foreach($data as $row) {
			$thesevalues = array_values($row);
			foreach($thesevalues as $key => $value)
				$thesevalues[$key] = '\''.$this->escape((string) $value).'\'';

			$values[] = implode(', ', $thesevalues);
		}

		$values = implode('), (', $values);

		return array($columns, $values);
	}

	protected function arrayToWhereSQL($data) {
		foreach((array) $data as $column => $value) {
			$pairs[] = "`$column`='$value'";
		}
		$where = implode(' AND ', $pairs);

		return $where;
	}

	/*
	 * These are lower level functions that are mainly just aliases for
	 * their normal MySQL functions.
	 */

	public function query($sql) {
		$this->connect();
		return mysql_query($sql, $this->link);
	}

	public function numRows($result) {
		$this->connect();
		return mysql_num_rows($result, $this->link);
	}

	public function fetch($result, $arraytype = MYSQL_BOTH) {
		$this->connect();
		if(is_resource($result))
			return mysql_fetch_array($result, $arraytype);
		else
			return false;
	}

	/**
	 * Escapes strings using mysql_real_escape_string().
	 * It can also recurse through single-dimension arrays.
	 */
	public function escape($unsafe) {
		$this->connect();

		if(is_string($unsafe) || is_numeric($unsafe))
			$safe = mysql_real_escape_string((string) $unsafe, $this->link);

		elseif(is_array($unsafe) && is_string(reset($unsafe)))
			foreach($unsafe as $key => $item)
				$safe[$key] = mysql_real_escape_string($item, $this->link);

		else return false;
		
		return (string) $safe;
	}
}
?>
