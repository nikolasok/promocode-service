<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Infrastructure\Database\Database;
use App\Infrastructure\Http\Message\RedirectResponse;
use App\Infrastructure\Http\Message\Request;
use App\Infrastructure\Http\Message\Response;
use App\Infrastructure\Template\TemplateService;
use App\Service\PromoCode\Exception\IpLimitReachedException;
use App\Service\PromoCode\PromoCodeService;

class PromoCodeController
{
    public function __construct(
        private PromoCodeService $promoCodeService,
        private TemplateService $templateService,
        private Database $database,
    ) {
    }

    public function index(): Response
    {
        return new Response($this->templateService->render('Service/Promocode/Template/promocode-form.php'));
    }

    public function getPromoCode(Request $request): Response
    {
        try {
            /** @var ?string $promoCode */
            $promoCode = $this->database->transactional(
                fn () => $this->promoCodeService->reservePromoCode($request->getDeviceId(), $request->getIpLong()),
            );
        } catch (IpLimitReachedException) {
            $promoCode = null;
        }

        if ($promoCode === null) {
            return new Response(
                $this->templateService->render('Service/Promocode/Template/promocode-form.php', [
                    'message' => 'There are no promo codes for you'
                ])
            );
        }

        return new RedirectResponse(
            sprintf("https://www.google.com/?%s", http_build_query(['query' => $promoCode]))
        );
    }
}
