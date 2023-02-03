<?php

declare(strict_types=1);

namespace Wherd\Database;

use IteratorAggregate;
use PDO;
use PDOStatement;
use Traversable;

/** @implements \IteratorAggregate<mixed> */
class Statement implements IteratorAggregate
{
    protected PDOStatement $statement;

    /** @param array<mixed> $params */
    public function __construct(
        protected Connection $connection,
        protected string $sql,
        protected array $params
    ) {
    }

    protected function getPdoStatement(): PDOStatement
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

        $this->statement = $this->connection->getPdo()->prepare($this->sql);

        foreach ($this->params as $key => $value) {
            $type = gettype($value);
            $this->statement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                $types[$type] ?? PDO::PARAM_STR
            );
        }

        $this->statement->execute();
        return $this->statement;
    }

    public function as(Fetch $fetchMode, mixed $extra = null): self
    {
        switch ($fetchMode) {
            case Fetch::Column:
                $this->getPdoStatement()->setFetchMode($fetchMode->value, $extra ?? 0);
                break;
            case Fetch::ToClass:
                $this->getPdoStatement()->setFetchMode($fetchMode->value, $extra);
                break;
            case Fetch::ToObject:
                $this->getPdoStatement()->setFetchMode($fetchMode->value, $extra);
                break;
            default:
                $this->getPdoStatement()->setFetchMode($fetchMode->value);
                break;
        }

        return $this;
    }

    public function getRowCount(): int
    {
        return $this->getPdoStatement()->rowCount();
    }

    public function execute(mixed ...$params): void
    {
        if (!empty($params)) {
            $this->params = $params;
        }

        if (!isset($this->statement)) {
            $this->getPdoStatement();
        } else {
            $this->statement->execute($params);
        }
    }

    public function fetch(): mixed
    {
        $result = $this->getPdoStatement()->fetch();
        if (!$result) {
            $this->close();
        }

        return $result;
    }

    public function fetchAll(): mixed
    {
        $result = $this->getPdoStatement()->fetchAll();
        $this->close();
        return $result;
    }

    public function close(): void
    {
        $this->getPdoStatement()->closeCursor();
    }

    public function getIterator(): Traversable
    {
        return $this->getPdoStatement()->getIterator();
    }

    public function __invoke(mixed ...$params): void
    {
        $this->execute(...$params);
    }
}
