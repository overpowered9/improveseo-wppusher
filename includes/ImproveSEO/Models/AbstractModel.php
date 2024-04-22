<?php

namespace ImproveSEO\Models;
if ( ! defined( 'ABSPATH' ) ) exit;

abstract class AbstractModel
{
	public $table;

	public $timestamps = true;

	public $offset;

	protected $casts;

	protected function escape($values)
	{
		foreach ($values as $key => $value) {
			$values[$key] = str_replace("\\r\\n", "\r\n", $value);
		}

		return $values;
	}

	public function __construct()
	{
		// Get table name from class name
		if (!$this->table) {
			preg_match("/([^\\\]+)$/i", get_class($this), $class);
			$class[1] = preg_replace("/y$/", "ie", $class[1]);
			$this->table = 'improveseo_'. strtolower($class[1]) .'s';
		}

		$this->offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
	}

	public function getTable()
	{
		global $wpdb;

		return $wpdb->prefix . $this->table;
	}

	public function create($data)
	{
		global $wpdb;

		$fields = array();
		$values = array();
		$vars = array();
        $tablename = $this->getTable();
		//$data = stripslashes_deep($data);

		$data = $this->escape($data);

		foreach ($data as $field => $value) {
			if (in_array($field, $this->fillable)) {
				$method = 'set'. ucfirst($field) .'Attribute';
				if (method_exists($this, $method)) $value = $this->$method($value);

				$fields[] = $field;
				$values[] = '%s';
				$vars[] = $value;
			}
		}

		$sql = "INSERT INTO tablename"." (". implode(", ", $fields) .", created_at)";

		$sql .= " VALUES (". implode(", ", $values) .", NOW())";
        $sql = str_replace("tablename",$tablename,$sql);
		$sql = $wpdb->prepare($sql, $vars);

		$wpdb->query($sql);

		return $wpdb->insert_id;
	}

	public function update($data, $id)
	{
		global $wpdb;

		$id = (int)$id;
		$fields = array();
		$vars = array();
		$data = $this->escape($data);
		$data = stripslashes_deep($data);
        $tablename = $this->getTable();
		foreach ($data as $field => $value) {
			if (in_array($field, $this->fillable)) {
				$method = 'set'. ucfirst($field) .'Attribute';
				if (method_exists($this, $method)) $value = $this->$method($value);

				$fields[] = "`$field` = %s";
				$vars[] = $value;
			}
		}

		if ($this->timestamps) $fields[] = "updated_at = NOW()";

		$sql = "UPDATE tablename";
		$sql .= " SET ". implode(", ", $fields);
		$sql .= " WHERE id = %d";
        $vars[] = $id;


		$sql = $wpdb->prepare($sql, $vars);
        $sql = str_replace("tablename",$tablename,$sql);
		$wpdb->query($sql);

		return true;
	}

	public function delete($id)
	{
		global $wpdb;
        $tablename = $this->getTable();

		$vars[] = $id;


        $sql = "DELETE FROM tablename WHERE id = %d";
        $sql = str_replace("tablename",$tablename,$sql);
        $sql = $wpdb->prepare($sql, $vars);
		$wpdb->query($sql);

		return true;
	}

	public function find($id)
	{
		global $wpdb;
        $tablename = $this->getTable();
		$sql = $wpdb->prepare("SELECT * FROM tablename WHERE id = %d",[$id]);
        $sql = str_replace("tablename",$tablename,$sql);
		$row = $wpdb->get_row($sql);

		// Type-cast
		if(!empty($this->casts)){
			if (sizeof($this->casts)) {
				foreach ($this->casts as $field => $type) {
					if ($type == 'array') {
						$row->$field = json_decode($row->$field, true);
					}
					elseif ($type == 'array|b64') {
						$row->$field = json_decode(base64_decode($row->$field), true);
					}
				}
			}
		}

		return $row;
	}

	public function all($orderBy = NULL)
	{
		global $wpdb;
        $tablename = $this->getTable();
        $sql = "SELECT * FROM tablename". ($orderBy ? " ORDER BY $orderBy" : "");
        $sql = str_replace("tablename",$tablename,$sql);
		return $wpdb->get_results($sql);
	}

	public function paginate($limit = 20)
	{
		global $wpdb;
        $tablename = $this->getTable();
		$sql = $wpdb->prepare("SELECT * FROM tablename LIMIT %d, %d", [$this->offset, $limit]);
        $sql = str_replace("tablename",$tablename,$sql);
		return $wpdb->get_results($sql);
	}

	public function count()
	{
        global $wpdb;
        $tablename = $this->getTable();
        $sql = "SELECT COUNT(id) AS total FROM tablename";
        $sql = str_replace("tablename",$tablename,$sql);
        $row = $wpdb->get_row($sql);
        return $row->total;
	}

	public function __call($method, $arguments)
	{
		global $wpdb;

		if (preg_match("/getBy(\w+)/i", $method, $condition)) {
			if (isset($condition[1])) {
				$field = strtolower($condition[1]);
                $tablename = $this->getTable();
                $sql = $wpdb->prepare("SELECT * FROM tablename WHERE `$field` = %s", [$arguments[0]]);
                $sql = str_replace("tablename",$tablename,$sql);
				return $wpdb->get_row($sql);
			}
		}
	}
}
