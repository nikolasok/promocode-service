<?php

use App\Http\Controller\PromoCodeController;

return FastRoute\simpleDispatcher(static function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', [PromoCodeController::class, 'index']);
    $r->addRoute('POST', '/', [PromoCodeController::class, 'getPromoCode']);
});
