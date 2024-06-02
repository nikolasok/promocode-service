<?php

use App\Command\GeneratePromoCodeCommand;
use App\Kernel;

require dirname(__DIR__).'/config/bootstrap.php';

$kernel = new Kernel(isDebug: true);
$kernel->boot();

$container = $kernel->getContainer();

/** @var GeneratePromoCodeCommand $command */
$command = $container->get(GeneratePromoCodeCommand::class);

$command->run();
