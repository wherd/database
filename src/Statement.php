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

    protected bool $executed = false;

    /** @param array<mixed> $params */
    public function __construct(
        protected Connection $connection,
        protected string $sql,
        protected array $params
    ) {
        static $types = [
            'boolean' => PDO::PARAM_BOOL,
            'integer' => PDO::PARAM_INT,
            'resource' => PDO::PARAM_LOB,
            'NULL' => PDO::PARAM_NULL,
        ];

        $this->statement = $this->connection->getPdo()->prepare($this->sql);

        foreach ($this->params as $key => $value) {
            $type = gettype($value);
            $this->statement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                $types[$type] ?? PDO::PARAM_STR
            );
        }
    }

    protected function getStatement(): PDOStatement
    {
        if (!$this->executed) {
            $this->executed = true;
            $this->statement->execute();
        }

        return $this->statement;
    }

    public function as(Fetch $fetchMode, mixed $extra = null): self
    {
        switch ($fetchMode) {
            case Fetch::Column:
                $this->statement->setFetchMode($fetchMode->value, $extra ?? 0);
                break;
            case Fetch::ToClass:
                $this->statement->setFetchMode($fetchMode->value, $extra);
                break;
            case Fetch::ToObject:
                $this->statement->setFetchMode($fetchMode->value, $extra);
                break;
            default:
                $this->statement->setFetchMode($fetchMode->value);
                break;
        }

        return $this;
    }

    public function getRowCount(): int
    {
        return $this->getStatement()->rowCount();
    }

    public function execute(mixed ...$params): bool
    {
        return $this->statement->execute($params);
    }

    public function fetch(): mixed
    {
        $result = $this->getStatement()->fetch();
        if (!$result) {
            $this->close();
        }

        return $result;
    }

    /** @return array<string,mixed> */
    public function fetchAll(): array
    {
        $result = $this->getStatement()->fetchAll();
        $this->close();
        return $result ?: [];
    }

    public function close(): void
    {
        $this->statement->closeCursor();
    }

    public function getIterator(): Traversable
    {
        return $this->getStatement()->getIterator();
    }

    public function __invoke(mixed ...$params): bool
    {
        return $this->execute(...$params);
    }
}
