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

		$sql = "INSERT INTO ". $this->getTable() ." (". implode(", ", $fields) .", created_at)";

		$sql .= " VALUES (". implode(", ", $values) .", NOW())";

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
		//$data = $this->escape($data);

		foreach ($data as $field => $value) {
			if (in_array($field, $this->fillable)) {
				$method = 'set'. ucfirst($field) .'Attribute';
				if (method_exists($this, $method)) $value = $this->$method($value);

				$fields[] = "`$field` = %s";
				$vars[] = $value;
			}
		}

		if ($this->timestamps) $fields[] = "updated_at = NOW()";

		$sql = "UPDATE ". $this->getTable();
		$sql .= " SET ". implode(", ", $fields);
		$sql .= " WHERE id = $id";

		$sql = $wpdb->prepare($sql, $vars);
		$wpdb->query($sql);

		return true;
	}

	public function delete($id)
	{
		global $wpdb;

		$sql = "DELETE FROM ". $this->getTable();
		$sql .= " WHERE id = %d";

		$vars[] = $id;

		$sql = $wpdb->prepare($sql, $vars);
		$wpdb->query($sql);

		return true;
	}

	public function find($id)
	{
		global $wpdb;

		$sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->table} WHERE id = %d", [$id]);

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

		return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$this->table}". ($orderBy ? " ORDER BY $orderBy" : ""));
	}

	public function paginate($limit = 20)
	{
		global $wpdb;

		$sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->table} LIMIT %d, %d", [$this->offset, $limit]);

		return $wpdb->get_results($sql);
	}

	public function count()
	{
		global $wpdb;

		$row = $wpdb->get_row("SELECT COUNT(id) AS total FROM {$wpdb->prefix}{$this->table}");
		return $row->total;
	}

	public function __call($method, $arguments)
	{
		global $wpdb;

		if (preg_match("/getBy(\w+)/i", $method, $condition)) {
			if (isset($condition[1])) {
				$field = strtolower($condition[1]);

				return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->table} WHERE `$field` = %s", [$arguments[0]]));
			}
		}
	}
}
