<?php

declare(strict_types=1);

use App\Infrastructure\Http\Message\Request;
use App\Kernel;

require dirname(__DIR__).'/config/bootstrap.php';

$kernel = new Kernel(isDebug: (bool)($_ENV['APP_DEBUG'] ?? 0));
$kernel->boot();

$handler = static function () use ($kernel) {
    $request = new Request($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    $response = $kernel->handle($request);
    $response->sendHeaders();
    echo $response->getContent();
};

for($nbRequests = 0, $running = true; $nbRequests < 1000 && $running; ++$nbRequests) {
    $running = frankenphp_handle_request($handler);

    // Do something after sending the HTTP response
    $kernel->terminate();

    // Call the garbage collector to reduce the chances of it being triggered in the middle of a page generation
    gc_collect_cycles();
}
