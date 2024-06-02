<?php

declare(strict_types=1);

namespace App\Service\PromoCode;

use App\Infrastructure\Lock\LockService;
use App\Service\PromoCode\Exception\IpLimitReachedException;
use App\Service\PromoCode\Repository\PromoCodeRepository;

final class PromoCodeService
{
    private const string LOCK_NAMESPACE = 'promocode';

    public function __construct(
        private LockService $lockService,
        private PromoCodeRepository $promoCodeRepository,
        private int $maxIpApplies,
    ) {
    }

    public function reservePromoCode(string $deviceId, int $ip): ?string
    {
        $lockIp = $this->lockService->lock(self::LOCK_NAMESPACE, (string)$ip);
        $lockDevice = $this->lockService->lock(self::LOCK_NAMESPACE, $deviceId);

        $oldCode = $this->promoCodeRepository->getPromoCodeByDevice($deviceId);
        if ($oldCode !== null) {
            return $oldCode;
        }

        $appliedIpCount = $this->promoCodeRepository->getAppliedIpCount($ip);
        if ($appliedIpCount >= $this->maxIpApplies) {
            throw new IpLimitReachedException();
        }

        $freePromoCode = $this->promoCodeRepository->getFreePromoCode();
        if ($freePromoCode === null) {
            return null;
        }

        $this->promoCodeRepository->updatePromoCodeInfo($freePromoCode, $deviceId, $ip, new \DateTimeImmutable());

        return $freePromoCode;
    }
}
