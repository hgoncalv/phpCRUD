<?php
// Include config.php file
require_once BASE_PATH . '/classes/Config.php';

class Database extends Config
{

    public $table = '';
    public $columns = [];
    public $limit;
    public $offset;
    public $orderby;
    public $ascdesc;
    public $what;
    public $like;

    public function get_cols()
    {
        $stmt = $this->conn->query("SHOW COLUMNS FROM $this->table");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $columns;
    }

    public function getTables()
    {
        $stmt = $this->conn->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function get_affected_rows_ids($where)
    {
        $read = $this->read($where);
        if (!$read) {
            return null;
        }

        $ids = array_map(function ($item) {
            return $item['id'];
        }, $read);
        return $ids;
    }

    public function buildWhereClause($table, $conditions)
    {
        $whereClause = '';
        if (!empty($conditions)) {
            $conditionsStrings = [];
            foreach ($conditions as $condition) {
                if (property_exists($table, $condition) && $table->$condition !== null) {
                    if (!$this->like) {
                        $conditionsStrings[] = "$condition = :$condition";
                    } else {
                        // Use placeholder without % symbols
                        $conditionsStrings[] = "$condition LIKE :$condition";
                    }
                }
            }
            // Return empty string if no conditions
            return empty($conditionsStrings) ? '' : implode(' AND ', $conditionsStrings);
        }
        // Return null if conditions are empty
        return null;
    }

    private function bindParameters($stmt, $columns)
    {
        foreach ($columns as $column) {
            if ($this->$column !== null) {
                if (!$this->like) {
                    $stmt->bindParam(':' . $column, $this->$column);
                } else {
                    $value = "%" . $this->$column . "%";
                    $stmt->bindParam(':' . $column, $value);
                }
            }
        }
    }

    private function buildSetClause()
    {
        // Filter columns with non-null values
        $updateColumns = array_filter(array_map(function ($column) {
            return ($this->$column !== null && $column != 'id') ? "$column = :$column" : null;
        }, $this->columns));

        // Check if there are columns to update
        if (empty($updateColumns)) {
            return false;
        }
        return $updateColumns;
    }

    private function checkWhereAndBuildWhereClause($where)
    {
        if (!$where || empty($this->columns)) {
            return false;
        }
        if (!($whereClause = $this->buildWhereClause($this, $where))) {
            return false;
        }
        return $whereClause;
    }

    public function read($where = null)
    {
        $sql = 'SELECT ';
        // Check if $this->what is an array and not empty
        if (is_array($this->what) && !empty($this->what)) {
            $columns = implode(', ', array_map(function ($col) {
                return "`$col`";
            }, $this->what));
            $sql .= $columns;
        } else {
            $sql .= '*';
        }
        $sql .= ' FROM `' . $this->table . '`';

        if (($whereClause = $this->checkWhereAndBuildWhereClause($where))) {
            $sql .= ' WHERE ' . $whereClause;
        }
        //ADD ORDER BY ASC/DESC
        $sql .= !empty($this->orderby) ? " ORDER BY $this->orderby" : '';
        $sql .= !empty($this->ascdesc) ? " $this->ascdesc" : '';
        // Add LIMIT and OFFSET if properties are set
        $sql .= !empty($this->limit) ? ' LIMIT :limit' : '';
        $sql .= !empty($this->offset) ? ' OFFSET :offset' : '';

        // Prepare the query
        $stmt = $this->conn->prepare($sql);
        // echo $sql;
        !empty($whereClause) ? $this->bindParameters($stmt, $where) : null;
        !empty($this->limit) ? $stmt->bindParam(':limit', $this->limit, PDO::PARAM_INT) : null;
        !empty($this->offset) ? $stmt->bindParam(':offset', $this->offset, PDO::PARAM_INT) : null;

        $stmt->execute();
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : null;
    }

    public function create($where = null)
    {
        // Ensure there is at least one column to insert
        if (empty($this->columns)) {
            return null;
        }

        // Filter columns with non-null, non-empty values
        $insertColumns = array_filter(array_map(function ($column) {
            $value = $this->$column;
            return (!empty($value)) ? $column : null;
        }, $this->columns));
        // Check if there are columns to insert
        if (empty($insertColumns)) {
            return null;
        }
        $columnsString = implode(', ', $insertColumns);

        $placeholders = array_map(function ($column) {
            return ':' . $column;
        }, $insertColumns);
        $placeholdersString = implode(', ', $placeholders);

        $sql = 'INSERT INTO ' . $this->table . ' (' . $columnsString . ') VALUES (' . $placeholdersString . ')';
        $stmt = $this->conn->prepare($sql);

        $this->bindParameters($stmt, $insertColumns);
        $stmt->execute();

        return ($stmt->rowCount() > 0) ? [$this->conn->lastInsertId()] : null;
    }

    public function update($where = null)
    {
        $whereBackup = $where;
        if (!($whereClause = $this->checkWhereAndBuildWhereClause($where))) {
            return null;
        }
        if (!($setClause = $this->buildSetClause())) {
            return null;
        }
        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $setClause);
        // Add 'where' condition to the SQL query dynamically (we know $where is not empty)
        $sql .= ' WHERE ' . $whereClause;
        $stmt = $this->conn->prepare($sql);
        $this->bindParameters($stmt, $this->columns);

        $stmt->execute();

        return ($stmt->rowCount() > 0) ? $this->get_affected_rows_ids($whereBackup) : null;
    }

    public function delete($where = null)
    {
        $ids = $this->get_affected_rows_ids($where);

        if (!($whereClause = $this->checkWhereAndBuildWhereClause($where))) {
            return null;
        }

        $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $whereClause;
        $stmt = $this->conn->prepare($sql);

        // Bind parameters for columns with non-null values
        $this->bindParameters($stmt, $where);

        // Execute the query
        $stmt->execute();
        return ($stmt->rowCount() > 0) ? $ids : null;
    }

}
