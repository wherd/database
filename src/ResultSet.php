<?php

/*
 * Database - Simple database layer.
 * Copyright (c) 2020 Wherd (https://www.wherd.dev).
 */

namespace Wherd\Database;

use PDO;

/** Represents a result set. */
class ResultSet
{
    /**
     * Database connection.
     * @var Connection
     */
    protected $connection;

    /**
     * Represents a prepared statement.
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * Measure query execution time.
     * @var float
     */
    protected $time;

    /**
     * The query string.
     * @var string
     */
    protected $sql;

    /**
     * Query parameters.
     * @var array<mixed>
     */
    protected $params;

    /**
     * Build a new ResultSet.
     * @param Connection $connection
     * @param string $sql
     * @param array<mixed> $params
     */
    public function __construct($connection, $sql, $params)
    {
        $this->connection = $connection;
        $this->sql = $sql;
        $this->params = $params;
    }
    
    /**
     * Execute the query and return the resultset.
     * @return \PDOStatement
     */
    protected function getPdoStatement()
    {
        static $types = [
            'boolean' => PDO::PARAM_BOOL,
            'integer' => PDO::PARAM_INT,
            'resource' => PDO::PARAM_LOB,
            'NULL' => PDO::PARAM_NULL,
        ];

        if (isset($this->statement)) {
            return $this->statement;
        }
        
        $time = microtime(true);
        $this->statement = $this->connection->getPdo()->prepare($this->sql);

        foreach ($this->params as $key => $value) {
            $type = gettype($value);
            $this->statement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                $types[$type] ?? PDO::PARAM_STR
            );
        }

        $this->statement->setFetchMode(PDO::FETCH_ASSOC);
        $this->statement->execute();
        $this->time = microtime(true) - $time;
        
        return $this->statement;
    }

    /**
     * Execute a statement.
     * @param mixed ...$params
     * @return self
     */
    public function execute(...$params)
    {
        if (!empty($params)) {
            $this->params = $params;
        }

        if (!isset($this->statement)) {
            $this->getPdoStatement();
        } else {
            $this->statement->execute($params);
        }

        return $this;
    }

    /**
     * Return column count.
     * @return int
     */
    public function getColumnCount()
    {
        return $this->getPdoStatement()->columnCount();
    }

    /**
     * Return row count.
     * @return int
     */
    public function getRowCount()
    {
        return $this->getPdoStatement()->rowCount();
    }

    /**
     * Return execution time.
     * @return float
     */
    public function getTime()
    {
        return $this->time ?? 0;
    }

    /**
     * Return single result row.
     * @return array<string,mixed>
     */
    public function fetch()
    {
        $stmt = $this->getPdoStatement();
        $data = $stmt->fetch();

        if (!$data) {
            $stmt->closeCursor();
        }

        return $data ?: [];
    }

    /**
     * Return single field from result.
     * @return mixed
     */
    public function fetchField()
    {
        $row = $this->fetch();
        return $row ? reset($row) : false;
    }

    /**
     * Return values from single result row.
     *  @return array<mixed>
     */
    public function fetchFields()
    {
        $row = $this->fetch() ?: [];
        return $row ? array_values($row) : [];
    }

    /**
     * Filter query result. Return indexed by key and with all fields or filtered by biven value.
     * @param string|null $key
     * @param string|null $value
     * @return array<mixed>
     */
    public function fetchPairs($key=null, $value=null)
    {
        return array_column($this->fetchAll(), $value, $key);
    }

    /**
     * Get all result data.
     * @return array<int,array<string,mixed>>
     */
    public function fetchAll()
    {
        $stmt = $this->getPdoStatement();

        $data = $stmt->fetchAll() ?: [];
        $stmt->closeCursor();

        return $data;
    }
}
