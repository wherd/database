<?php

/*
 * Database - Simple database layer.
 * Copyright (c) 2020 Wherd (https://www.wherd.dev).
 */

namespace Wherd\Database;

use PDO;

class Connection
{
    /**
     * Database connection.
     * @var string
     */
    protected $dsn;

    /**
     * Database connection username.
     * @var string|null
     */
    protected $username;

    /**
     * Database connection password.
     * @var string|null
     */
    protected $password;

    /**
     * Options to pass to connection.
     * @var array<string,mixed>|null
     */
    protected $options;

    /**
     * Connection resource.
     * @var PDO
     */
    protected $pdo;

    /**
     * Last executed query.
     * @var string
     */
    protected $sql;

    /**
     * Create a database connection.
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array<string,mixed>|null $options
     */
    public function __construct($dsn, $username=null, $password=null, $options=null)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;
    }
    
    /**
     * Return a connection between PHP and a database server.
     * @return PDO
     */
    public function getPdo()
    {
        if (empty($this->pdo)) {
            $this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $this->pdo;
    }
    
    /**
     * Returns the ID of the last inserted row or sequence value.
     * @param string $sequence
     * @return string
     */
    public function getInsertId($sequence='')
    {
        $res = $this->getPdo()->lastInsertId($sequence);
        return '' === $res ? '0' : $res;
    }
    
    /**
     * Quotes a string for use in a query.
     * @param string $string,
     * @param int $type
     * @return string
     */
    public function quote($string, $type=PDO::PARAM_STR)
    {
        return $this->getPdo()->quote($string, $type);
    }
    
    /**
     * Initiates a transaction.
     * @return self
     */
    public function beginTransaction()
    {
        $this->getPdo()->beginTransaction();
        return $this;
    }

    /**
     * Commits a transaction.
     * @return self
     */
    public function commit()
    {
        $this->getPdo()->commit();
        return $this;
    }

    /**
     * Rolls back a transaction.
     * @return self
     */
    public function rollBack()
    {
        $this->getPdo()->rollBack();
        return $this;
    }
    
    /**
     * Run function wrapped on a database transaction.
     * @param callable $callback
     * @return mixed
     */
    public function transaction($callback)
    {
        $this->beginTransaction();
        $result = null;

        try {
            $result = $callback();
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }

        $this->commit();
        return $result;
    }
    
    /**
     * Generates and executes SQL query.
     * @param string $sql
     * @param mixed ...$params
     * @return ResultSet
     */
    public function query($sql, ...$params)
    {
        $this->sql = $sql;
        return new ResultSet($this, $this->sql, $params);
    }

    /**
     * Return last executed query.
     * @return string
     */
    public function getLastQueryString()
    {
        return $this->sql;
    }

    // Shortcuts --------------------------------------------------------------

    /**
     * Execute a query.
     * @param string $sql
     * @param mixed ...$params
     * @return ResultSet
     */
    public function execute($sql, ...$params)
    {
        return $this->query($sql)->execute(...$params);
    }

    /**
     * Return single result row.
     * @param string $sql
     * @param mixed ...$params
     * @return array<string,mixed>
     */
    public function fetch($sql, ...$params)
    {
        return $this->query($sql, ...$params)->fetch();
    }

    /**
     * Return single field from result.
     * @param string $sql
     * @param mixed ...$params
     * @return mixed
     */
    public function fetchField($sql, ...$params)
    {
        return $this->query($sql, ...$params)->fetchField();
    }

    /**
     * Return values from single result row.
     * @param string $sql
     * @param mixed ...$params
     * @return array<mixed>
     */
    public function fetchFields($sql, ...$params)
    {
        return $this->query($sql, ...$params)->fetchFields();
    }

    /**
     * Get all result data.
     * @param string $sql
     * @param mixed ...$params
     * @return array<int,array<string,mixed>>
     */
    public function fetchAll($sql, ...$params)
    {
        return $this->query($sql, ...$params)->fetchAll();
    }
}
