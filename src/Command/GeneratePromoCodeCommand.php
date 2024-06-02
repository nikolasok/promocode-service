<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Database\Database;
use App\Infrastructure\Database\Exception\StmtException;
use Random\Randomizer;

class GeneratePromoCodeCommand
{
    public function __construct(private Database $database)
    {
    }

    public function run(): void
    {
        $rng = new Randomizer();
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chunkSize = 5000;

        $pdo = $this->database->getPdo();

        $k = 0;
        while ($k < 500_000) {
            $values = [];
            for($i = 0; $i < $chunkSize ; $i++) {
                $res = $rng->getBytesFromString($alphabet, 10);
                $values[] = $res;
            }

            $stmt = $pdo->prepare(
                'INSERT IGNORE INTO promocode (code) VALUES'
                . implode(',', array_fill(0, \count($values), '(?)'))
            );
            if ($stmt === false) {
                throw new StmtException();
            }
            $this->database->transactional(static fn () => $stmt->execute($values));
            $k += $chunkSize;
        }
    }
}
