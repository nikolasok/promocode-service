<?php

declare(strict_types=1);

namespace App\Service\PromoCode\Repository;

use App\Infrastructure\Database\Database;
use App\Infrastructure\Database\Exception\StmtException;

class PromoCodeRepository
{
    public function __construct(private Database $database)
    {
    }

    public function getFreePromoCode(): ?string
    {
        $pdo = $this->database->getPdo();
        $stmt = $pdo->query('SELECT p.code as code FROM promocode p WHERE p.device_id IS NULL LIMIT 1 FOR UPDATE SKIP LOCKED');
        if ($stmt === false) {
            throw new StmtException();
        }
        /** @var array<string, string> $res */
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res['code'] ?? null;
    }

    public function getPromoCodeByDevice(string $deviceId): ?string
    {
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare('SELECT p.code FROM promocode p WHERE p.device_id = :device_id');
        if ($stmt === false) {
            throw new StmtException();
        }
        $stmt->bindParam(':device_id', $deviceId);
        $stmt->execute();
        /** @var array<string, string> $res */
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $res['code'] ?? null;
    }

    public function getAppliedIpCount(int $ip): int
    {
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare(
            'SELECT count(p.ip_long) as count FROM promocode p WHERE p.ip_long = :ip'
        );
        if ($stmt === false) {
            throw new StmtException();
        }
        $stmt->bindParam(':ip', $ip, \PDO::PARAM_INT);
        $stmt->execute();
        /** @var array<string, int> $res */
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $res['count'];
    }

    public function updatePromoCodeInfo(string $promoCode, string $deviceId, int $ip, \DateTimeImmutable $appliedAt): int
    {
        $appliedAtTimestamp = $appliedAt->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare(
            'UPDATE promocode p SET p.device_id = :device_id, p.ip_long = :ip, p.applied_at = :applied_at WHERE p.code = :code'
        );
        if ($stmt === false) {
            throw new StmtException();
        }

        $stmt->execute([
            'device_id' => $deviceId,
            'ip' => $ip,
            'applied_at' => $appliedAtTimestamp,
            'code' => $promoCode,
        ]);

        return $stmt->rowCount();
    }
}
