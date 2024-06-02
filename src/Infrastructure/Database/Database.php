<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

final class Database
{
    private ?\PDO $pdo = null;
    protected int $transactionLevel = 0;

    public function __construct(
        private string $dsn,
        private string $username,
        #[\SensitiveParameter] private string $password,
    ) {
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function getPdo(): \PDO
    {
        if ($this->pdo === null) {
            return $this->connect();
        }

        return $this->pdo;
    }

    public function connect(): \PDO
    {
        $this->pdo ??= $this->createPdo();

        return $this->pdo;
    }

    public function disconnect(): void
    {
        if ($this->pdo !== null) {
            $this->pdo = null;
        }
    }

    public function transactional(callable $fn): mixed
    {
        $this->beginTransaction();
        try {
            $res = $fn();
            $this->commit();
            return $res;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function beginTransaction(): void
    {
        if($this->transactionLevel === 0) {
            $this->getPdo()->beginTransaction();
        } else {
            $this->getPdo()->exec("SAVEPOINT LEVEL{$this->transactionLevel}");
        }

        $this->transactionLevel++;
    }

    public function commit(): void
    {
        $this->transactionLevel--;

        if($this->transactionLevel === 0) {
            $this->getPdo()->commit();
        } else {
            $this->getPdo()->exec("RELEASE SAVEPOINT LEVEL{$this->transactionLevel}");
        }
    }

    public function rollBack(): void
    {
        if ($this->transactionLevel === 0) {
            throw new \LogicException('Rollback error : There is no transaction started');
        }

        $this->transactionLevel--;

        if($this->transactionLevel === 0) {
            $this->getPdo()->rollBack();
        } else {
            $this->getPdo()->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transactionLevel}");
        }
    }

    private function createPdo(): \PDO
    {
        return new \PDO(
            $this->dsn,
            $this->username,
            $this->password,
            [\PDO::ATTR_AUTOCOMMIT => false],
        );
    }
}
