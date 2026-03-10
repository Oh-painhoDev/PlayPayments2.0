<?php

namespace App\Helpers;

class DocumentHelper
{
    /**
     * Formata CPF ou CNPJ
     */
    public static function format($document)
    {
        if (!$document) {
            return 'N/A';
        }
        
        // Remove caracteres não numéricos
        $document = preg_replace('/[^0-9]/', '', $document);
        
        // CPF (11 dígitos)
        if (strlen($document) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document);
        }
        
        // CNPJ (14 dígitos)
        if (strlen($document) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document);
        }
        
        // Retorna sem formatação se não for CPF nem CNPJ
        return $document;
    }
}

