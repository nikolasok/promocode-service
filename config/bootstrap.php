<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(dirname(__DIR__) . '/.env');
if (isset($_SERVER['APP_ENV']) && is_file(__DIR__) . '/.env_' . $_SERVER['APP_ENV']) {
    $dotenv->load(dirname(__DIR__) . '/.env_' . $_SERVER['APP_ENV']);
}
