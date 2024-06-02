<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\PromoCode;

use App\Infrastructure\Lock\LockService;
use App\Service\PromoCode\Exception\IpLimitReachedException;
use App\Service\PromoCode\PromoCodeService;
use App\Service\PromoCode\Repository\PromoCodeRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PromoCodeServiceTest extends TestCase
{
    private LockService & MockObject $lockService;
    private PromoCodeRepository & MockObject $promoCodeRepository;

    protected function setUp(): void
    {
        $this->lockService = $this->createMock(LockService::class);
        $this->promoCodeRepository = $this->createMock(PromoCodeRepository::class);
    }

    public function testGetOldCode(): void
    {
        $service = $this->getService();
        $this->promoCodeRepository->expects(self::once())->method('getPromoCodeByDevice')->willReturn($stubCode = 'someCode');
        $code = $service->reservePromoCode('1', 1);
        self::assertSame($stubCode, $code);
    }

    public function testIpLimitReached(): void
    {
        $service = $this->getService();
        $this->promoCodeRepository->expects(self::once())->method('getPromoCodeByDevice')->willReturn(null);
        $this->promoCodeRepository->expects(self::once())->method('getAppliedIpCount')->willReturn(1001);
        self::expectException(IpLimitReachedException::class);
        $service->reservePromoCode('1', 1);
    }

    public function testNoFreeCode(): void
    {
        $service = $this->getService();
        $this->promoCodeRepository->expects(self::once())->method('getPromoCodeByDevice')->willReturn(null);
        $this->promoCodeRepository->expects(self::once())->method('getAppliedIpCount')->willReturn(999);
        $this->promoCodeRepository->expects(self::once())->method('getFreePromoCode')->willReturn(null);
        $code = $service->reservePromoCode('1', 1);
        self::assertNull($code);
    }

    public function testGetFreePromoCode(): void
    {
        $service = $this->getService();
        $this->promoCodeRepository->expects(self::once())->method('getPromoCodeByDevice')->willReturn(null);
        $this->promoCodeRepository->expects(self::once())->method('getAppliedIpCount')->willReturn(999);
        $this->promoCodeRepository->expects(self::once())->method('getFreePromoCode')->willReturn($stubCode = '123');
        $this->promoCodeRepository->expects(self::once())->method('updatePromoCodeInfo')->with('123', '1', 1)->willReturn(1);
        $code = $service->reservePromoCode('1', 1);
        self::assertSame($stubCode, $code);
    }

    private function getService(int $maxIpApplies = 1000): PromoCodeService
    {
        return new PromoCodeService(
            $this->lockService,
            $this->promoCodeRepository,
            $maxIpApplies,
        );
    }
}
