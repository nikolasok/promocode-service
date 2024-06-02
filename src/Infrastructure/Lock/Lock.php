<?php

declare(strict_types=1);

namespace App\Infrastructure\Lock;

use App\Infrastructure\Database\Database;
use App\Infrastructure\Database\Exception\LockTimeout;
use App\Infrastructure\Database\Exception\StmtException;

class Lock
{
    private string $key;

    public function __construct(
        private Database $database,
        string $namespace,
        string $key,
    ) {
        $this->key = $namespace . ':' . $key;
    }

    public function __destruct()
    {
        $this->release();
    }

    public function acquire(): self
    {
        $key = $this->key;
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare('SELECT GET_LOCK(:key, 5) as is_success');
        if ($stmt === false) {
            throw new StmtException();
        }
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        /** @var array<string, int> $res */
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$res['is_success']) {
            throw new LockTimeout("Can't acquire lock for resource $key");
        }

        return $this;
    }

    public function release(): void
    {
        $key = $this->key;
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare('SELECT RELEASE_LOCK(:key) as is_success');
        if ($stmt === false) {
            throw new StmtException();
        }
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        /** @var array<string, int> $res */
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$res['is_success']) {
            throw new LockTimeout("Can't release lock for resource $key");
        }
    }
}
