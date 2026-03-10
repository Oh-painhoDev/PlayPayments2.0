<?php

namespace App\Services\Acquirers;

interface AcquirerInterface
{
    public function createPix(array $payload);
    public function getTransaction(string $id);
    public function verifySignature(array $postData): bool; // ✅ verificação de segurança
}
