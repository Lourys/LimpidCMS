<?php
(defined('BASEPATH')) or exit('No direct script access allowed');

/**
 * Model CMS class
 *
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 */
class CMS_Model extends CI_Model
{
  /**
   * Name of the database table
   *
   * @var string
   */
  protected $table = '';

  /**
   * Primary key of database table
   *
   * @var string
   */
  protected $primaryKey = 'id';

  /**
   * Temporary array for returning
   *
   * @var array
   */
  protected $data = array();

  /**
   * True, if relational data is being called
   *
   * @var boolean
   */
  protected $getRelation = false;

  /**
   * Result type fetched by queries
   *
   * @var string
   */
  protected $resultType = 'object';

  /**
   * Result fetched is single row or multiple
   *
   * @var string
   */
  protected $rowType = 'multiple';

  /**
   * This is a collection of relations added in model class
   *
   * @var array
   */
  protected $relations = array();

  /**
   * Set automatic table name based on name of model class
   */
  function __construct()
  {
    parent::__construct();
    if ($this->table == '') {
      $this->table = $this->classToTable(get_called_class());
    }
  }

  /**
   * Convert class name to assuming database table name
   *
   * @param string $string
   *
   * @return string
   */
  private function classToTable($string)
  {
    return strtolower(str_replace('_model', '', $string));
  }

  /**
   * For getting the name of table
   *
   * @return string
   */
  public function getTable()
  {
    return $this->table;
  }

  /**
   * Send the result of raw query passed
   *
   * @param string $query
   * @param string $type
   *
   * @return array|bool
   */
  public function rawQuery($query, $type = 'object')
  {
    switch ($type) {
      case 'array':
        return $this->db->query($query)->result_array();
      case 'row_array':
        return $this->db->query($query)->row_array();
      case 'row_object':
        return $this->db->query($query)->row_object();
      case 'none':
        return $this->db->query($query);
      default:
        return $this->db->query($query)->result();
    }
  }

  /**
   * Return the last query
   *
   * @return string
   */
  public function lastQuery()
  {
    return $this->db->last_query();
  }

  /**
   * Count all rows present in table
   *
   * @return integer
   */
  public function countAll()
  {
    return $this->db->count_all($this->table);
  }

  /**
   * Get all rows present in table
   *
   * @return array
   */
  public function getAll()
  {
    return $this->db->get($this->table)->result();
  }

  /**
   * Get all rows present in table limited
   *
   * @param int $limit
   * @param int $offset
   *
   * @return array
   */
  public function getAllLimited($limit, $offset)
  {
    return $this->db->get($this->table, $limit, $offset)->result();
  }

  /**
   * Get all rows present in table limited ordered
   *
   * @param int $limit
   * @param int $offset
   * @param string $order_by
   * @param string $order
   *
   * @return array
   */
  public function getAllLimitedOrdered($limit, $offset, $order_by, $order = 'ASC')
  {
    return $this->db->order_by($order_by, $order)->get($this->table, $limit, $offset)->result();
  }

  /**
   * Get all rows present in table ordered
   *
   * @param string $order_by
   * @param string $order
   *
   * @return array
   */
  public function getAllOrdered($order_by, $order = 'ASC')
  {
    return $this->db->order_by($order_by, $order)->get($this->table)->result();
  }

  /**
   * Get rows based on array of conditions passed
   *
   * @param array $conditions
   * @param string $type
   *
   * @return array|object|null
   */
  public function getWhere($conditions, $type = 'object')
  {
    if (is_array($conditions)) {
      switch ($type) {
        case 'array':
          return $this->db->get_where($this->table, $conditions)->result_array();
        case 'row_array':
          return $this->db->get_where($this->table, $conditions)->row_array();
        case 'row_object':
          return $this->db->get_where($this->table, $conditions)->row_object();
        default:
          return $this->db->get_where($this->table, $conditions)->result();
      }

    }

    return null;
  }

  /**
   * Get rows based on array of conditions passed and ordered
   *
   * @param array $conditions
   * @param string $order_by
   * @param string $order
   * @param string $type
   *
   * @return array|object|null
   */
  public function getWhereOrdered($conditions, $order_by, $order = 'ASC', $type = 'object')
  {
    if (is_array($conditions)) {
      switch ($type) {
        case 'array':
          return $this->db->order_by($order_by, $order)->get_where($this->table, $conditions)->result_array();
        case 'row_array':
          return $this->db->order_by($order_by, $order)->get_where($this->table, $conditions)->row_array();
        case 'row_object':
          return $this->db->order_by($order_by, $order)->get_where($this->table, $conditions)->row_object();
        default:
          return $this->db->order_by($order_by, $order)->get_where($this->table, $conditions)->result();
      }

    }

    return null;
  }

  /**
   * Get rows based on array of conditions passed and limited
   *
   * @param array $conditions
   * @param int $limit
   * @param int $offset
   * @param string $type
   *
   * @return array|object|null
   */
  public function getWhereLimited($conditions, $limit = 0, $offset = 0, $type = 'object')
  {
    if (is_array($conditions)) {
      switch ($type) {
        case 'array':
          return $this->db->get_where($this->table, $conditions, $limit, $offset)->result_array();
        case 'row_array':
          return $this->db->get_where($this->table, $conditions, $limit, $offset)->row_array();
        case 'row_object':
          return $this->db->get_where($this->table, $conditions, $limit, $offset)->row_object();
        default:
          return $this->db->get_where($this->table, $conditions, $limit, $offset)->result();
      }

    }

    return null;
  }

  /**
   * Get rows based on multiple IDs passed with column name
   *
   * @param array $IDsArray
   * @param string $column
   * @param string $type
   *
   * @return array|null
   */
  public function getWhereIDs($IDsArray, $column = 'id', $type = 'object')
  {
    if ($IDsArray) {
      if (is_array($IDsArray)) {
        if ($type == 'array') {
          return $this->db->from($this->table)->where_in($column, $IDsArray)->get()->result_array();
        } else {
          return $this->db->from($this->table)->where_in($column, $IDsArray)->get()->result();
        }
      }
    }

    return null;
  }

  /**
   * Get single row based on id or condition is passed
   *
   * @param integer|array $id
   * @param array $fields
   *
   * @return object|null
   */
  public function find($id, $fields = array())
  {
    if ($id) {
      if (is_array($id)) {
        if (empty($fields)) {
          return $this->db->get_where($this->table, $id)->row();
        } else {
          $fields = implode(", ", array_values($fields));
          return $this->db->select($fields)->get_where($this->table, $id)->row();
        }
      } else {
        if (empty($fields)) {
          return $this->db->get_where($this->table, array($this->primaryKey => $id))->row();
        } else {
          $fields = implode(", ", array_values($fields));
          return $this->db->select($fields)->get_where($this->table, array($this->primaryKey => $id))->row();
        }
      }
    }

    return null;
  }

  /**
   * Return maximum of column values
   *
   * @param string $column
   *
   * @return integer
   */
  public function max($column)
  {
    return $this->db->select_max($column)->get($this->table)->result()[0]->$column;
  }

  /**
   * Return minimum of column values
   *
   * @param string $column
   *
   * @return integer
   */
  public function min($column)
  {
    return $this->db->select_min($column)->get($this->table)->result()[0]->$column;
  }

  /**
   * Return average of column values
   *
   * @param string $column
   *
   * @return integer
   */
  public function avg($column)
  {
    return $this->db->select_avg($column)->get($this->table)->result()[0]->$column;
  }

  /**
   * Return sum of column values
   *
   * @param string $column
   *
   * @return integer
   */
  public function sum($column)
  {
    return $this->db->select_sum($column)->get($this->table)->result()[0]->$column;
  }

  /**
   * Insert single row in table
   *
   * @param array $data
   *
   * @return integer|boolean
   */
  public function insert($data = array())
  {
    if (sizeof($data) > 0) {
      $this->db->insert($this->table, $data);
      return $this->db->insert_id();
    }

    return false;
  }

  /**
   * Insert multiple rows in table
   *
   * @param array $data
   *
   * @return boolean
   */
  public function insertBatch($data = array())
  {
    if (sizeof($data) > 0) {
      return $this->db->insert_batch($this->table, $data);
    }

    return false;
  }

  /**
   * Update a row in table by id or condition
   *
   * @param integer|array $id
   * @param array $data
   *
   * @return boolean
   */
  public function update($id, $data = array())
  {
    if ($id && sizeof($data) > 0) {
      if (is_array($id)) {
        return $this->db->where($id)->update($this->table, $data);
      } else {
        return $this->db->where($this->primaryKey, $id)->update($this->table, $data);
      }
    }

    return false;
  }

  /**
   * Delete single row by id or conditions
   *
   * @param integer|array $id
   *
   * @return boolean
   */
  public function delete($id)
  {
    if ($id) {
      if (is_array($id)) {
        return $this->db->where($id)->delete($this->table);
      } else {
        return $this->db->where($this->primaryKey, $id)->delete($this->table);
      }
    }

    return false;
  }

  /**
   * Delete multiple rows by conditions
   *
   * @param array $conditions
   *
   * @return boolean
   */
  public function deleteBatch($conditions)
  {
    if ($conditions) {
      if (is_array($conditions)) {
        $IDsArray = $this->getIDsArray($conditions);
        $this->db->where_in($this->primaryKey, $IDsArray);
        return $this->db->delete($this->table);
      }
    }

    return false;
  }

  /**
   * Delete multiple rows by IDs
   *
   * @param array $IDs
   *
   * @return boolean
   */
  public function deleteIDs($IDs)
  {
    if ($IDs) {
      if (is_array($IDs)) {
        $this->db->where_in($this->primaryKey, $IDs);
        return $this->db->delete($this->table);
      }
    }

    return false;
  }

  /**
   * Delete all rows from the table
   *
   * @return boolean
   */
  public function truncate()
  {
    return $this->db->truncate($this->table);
  }

  /**
   * Get all the IDs of array of result
   *
   * @param array $conditions
   *
   * @return array
   */
  public function getIDsArray($conditions)
  {
    $result = $this->db->select($this->primaryKey)->get_where($this->table, $conditions)->result_array();
    $IDs = array();

    if (sizeof($result) > 0) {
      foreach ($result as $res) {
        $IDs[] = $res[$this->primaryKey];
      }
    }

    return $IDs;
  }

  /**
   * Check if row exist with column value
   *
   * @param string $column
   * @param mixed $value
   * @param integer $id
   *
   * @return boolean
   */
  public function checkUnique($column, $value, $id = 0)
  {
    if ($id == 0) {
      $row = $this->db->get_where($this->table, array($column => $value))->num_rows();
    } else {
      $row = $this->db->get_where($this->table, array($this->primaryKey . ' !=' => $id, $column => $value))->num_rows();
    }

    if ($row > 0) {
      return true;
    }

    return false;
  }

  /**
   * Set relations as true for fetching relational result
   *
   * @return object
   */
  public function relate()
  {
    $this->getRelation = true;

    return $this;
  }

  /**
   * Get all the IDs of the result
   *
   * @param array $result
   * @param string $id
   * @param string $column
   *
   * @return array
   */
  public function getResultIDsArray($result, $id = 'id', $column = '')
  {
    $IDs = array();

    if (sizeof($result) > 0) {
      foreach ($result as $res) {
        if (is_object($res)) {
          if (isset($res->$column)) {
            return $IDs;
          }
          $this->resultType = 'object';
          $IDs[] = $res->$id;
        } else if (is_array($res)) {
          if (isset($res[$column])) {
            return $IDs;
          }
          $this->resultType = 'array';
          $IDs[] = $res[$id];
        } else {
          $this->rowType = 'single';
          if (isset($result->$id)) {
            $IDs[] = $result->$id;
            break;
          } else if (isset($result[$id])) {
            $IDs[] = $result[$id];
            break;
          }
        }
      }
    }

    return $IDs;
  }

  /**
   * Add relation to database table
   *
   * @param array $relation
   */
  protected function addRelation($relation = array())
  {
    if (sizeof($relation) > 0) {
      $this->relations[] = $relation;
    }
  }

  /**
   * Add multiple relations to database table
   *
   * @param array $relations
   */
  protected function addRelations($relations = array())
  {
    if (sizeof($relations) > 0) {
      foreach ($relations as $relation) {
        $this->relations[] = $relation;
      }
    }
  }

  /**
   * Process and find relations to the result data
   *
   * @param array $result
   *
   * @return array
   */
  protected function processRelations($result)
  {
    if (sizeof($this->relations) > 0) {
      foreach ($this->relations as $relation) {
        $primary = isset($relation['primary']) ? $relation['primary'] : $this->primaryKey;
        $foreign = isset($relation['foreign']) ? $relation['foreign'] : $this->table . '_id';
        $variable = isset($relation['variable']) ? $relation['variable'] : '';
        $single = isset($relation['single']) ? $relation['single'] : false;
        $column = isset($relation['column']) ? $relation['column'] : '';

        if ($variable == '' && isset($relation['table'])) {
          $variable = strtolower($relation['table']);
        }

        if (isset($relation['table'])) {
          $result = $this->attachRelation($result, $primary, $foreign, $variable, $relation['table'], 'table', $column, $single);
        } else if (isset($relation['model'])) {
          $result = $this->attachRelation($result, $primary, $foreign, $variable, $relation['model'], 'model', $column, $single);
        }
      }
    }

    return $result;
  }

  /**
   * Add relational data to result data
   *
   * @param array $result
   * @param string $primary
   * @param string $foreign
   * @param string $variable
   * @param string $table
   * @param string $type
   * @param string $column
   * @param boolean $single
   *
   * @return array
   */
  protected function attachRelation($result, $primary, $foreign, $variable, $table, $type = 'table', $column = '', $single = false)
  {
    $IDs = $this->getResultIDsArray($result, $primary, $variable);

    if (sizeof($IDs) > 0) {
      if ($type == 'model') {
        $this->load->model($table);
        $rows = $this->$table->getWhereIDs($IDs, $foreign, $this->resultType);
      } else {
        $IDS = implode(',', $IDs);
        $rows = $this->rawQuery("SELECT * FROM {$table} WHERE {$foreign} IN({$IDS})", $this->resultType);
      }
      foreach ($result as $key => $res) {
        if ($this->resultType == 'object') {
          if ($this->rowType == 'single') {
            $result->{$variable} = $this->extractRelatedRows($rows, $result->$primary, $foreign, $column, $single);
          } else {
            $result[$key]->{$variable} = $this->extractRelatedRows($rows, $res->$primary, $foreign, $column, $single);
          }
        } else if ($this->resultType == 'array') {
          if ($this->rowType == 'single') {
            $result[$variable] = $this->extractRelatedRows($rows, $result[$primary], $foreign, $column, $single);
          } else {
            $result[$key][$variable] = $this->extractRelatedRows($rows, $res[$primary], $foreign, $column, $single);
          }
        }
      }
    }

    return $result;
  }

  /**
   * Find and get related rows from other tables
   *
   * @param  array|object $rows
   * @param  integer $primaryID
   * @param  string $foreign
   * @param  string $column
   * @param  boolean $single
   *
   * @return array|object|string
   */
  protected function extractRelatedRows($rows, $primaryID, $foreign, $column = '', $single = false)
  {
    $result = new stdClass();

    if ($this->resultType == 'array') {
      $result = array();
    }

    if ($column != '') {
      $result = '';
    }

    if (sizeof($rows) > 0) {
      $i = 0;
      foreach ($rows as $row) {
        if ($single) {
          return $row;
        }
        if ($this->resultType == 'object') {
          if ($row->$foreign == $primaryID) {
            if ($column == '') {
              $result->{$i} = $row;
            } else {
              return $this->getColumnValue($row, $column);
            }
            $i++;
          }
        } else if ($this->resultType == 'array') {
          if ($row[$foreign] == $primaryID) {
            if ($column == '') {
              $result[$i] = $row;
            } else {
              return $this->getColumnValue($row, $column);
            }
            $i++;
          }
        }
      }

      return $result;
    }

    return null;
  }

  /**
   * Get the column value based on type
   *
   * @param  object $row
   * @param  string $column
   *
   * @return string $column
   */
  protected function getColumnValue($row, $column)
  {
    $value = '';

    if ($this->resultType == 'object') {
      if (is_array($column)) {
        if (isset($column['modify'])) {
          $value = str_replace("_COL_", $row->$column['name'], $column['modify']);
        } else {
          $value = $row->$column['name'];
        }
      } else {
        $value = $row->$column;
      }
    } else if ($this->resultType == 'array') {
      if (is_array($column)) {
        if (isset($column['modify'])) {
          $value = str_replace("_COL_", $row[$column['name']], $column['modify']);
        } else {
          $value = $row[$column['name']];
        }
      } else {
        $value = $row[$column];
      }
    }

    return $value;
  }

}
