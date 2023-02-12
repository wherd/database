<?php

declare(strict_types=1);

namespace Wherd\Database;

use Closure;
use PDO;

class Connection
{
    protected PDO $connection;

    /** @param array<string,mixed>|null $options */
    public function __construct(
        protected string $dsn,
        protected ?string $username = null,
        protected ?string $password = null,
        protected ?array $options = null,
        protected ?Closure $callback = null
    ) {
    }

    public function getPdo(): PDO
    {
        if (empty($this->connection)) {
            $this->connection = new PDO(
                $this->dsn,
                $this->username,
                $this->password,
                $this->options
            );

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (null !== $this->callback) {
                call_user_func($this->callback, $this->connection);
            }
        }

        return $this->connection;
    }

    public function getLastInsertId(): int
    {
        $res = $this->getPdo()->lastInsertId();
        return (int) ($res ?: 0);
    }

    public function beginTransaction(): bool
    {
        return $this->getPdo()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getPdo()->commit();
    }

    public function rollback(): bool
    {
        return $this->getPdo()->rollBack();
    }

    public function prepare(string $sql, mixed ...$params): Statement
    {
        return new Statement($this, $sql, $params);
    }

    // Shorthands

    /** @return array<string,mixed> */
    public function fetchAll(string $sql, mixed ...$params): array
    {
        return $this->prepare($sql, ...$params)->fetchAll();
    }

    public function fetch(string $sql, mixed ...$params): mixed
    {
        $stmt = $this->prepare($sql, ...$params);
        $result = $stmt->fetch();

        $stmt->close();
        return $result;
    }

    public function fetchColumn(string $sql, mixed ...$params): mixed
    {
        $stmt = $this->prepare($sql, ...$params)->as(Fetch::Column);
        $result = $stmt->fetch();
        $stmt->close();

        return $result;
    }

    public function insert(string $sql, mixed ...$params): ?int
    {
        $stmt = $this->prepare($sql);
        $result = $stmt->execute(...$params);
        $stmt->close();

        return $result ? $this->getLastInsertId() : null;
    }

    public function execute(string $sql, mixed ...$params): bool
    {
        $stmt = $this->prepare($sql);
        $result = $stmt->execute(...$params);
        $stmt->close();

        return $result;
    }
}
