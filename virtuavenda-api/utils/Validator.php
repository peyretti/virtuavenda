<?php

namespace App\Utils;

class Validator
{
    /**
     * Valida uma URL
     * 
     * @param string $url URL para validar
     * @return bool Verdadeiro se a URL for válida
     */
    public static function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valida um endereço de e-mail
     * 
     * @param string $email E-mail para validar
     * @return bool Verdadeiro se o e-mail for válido
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida um CPF
     * 
     * @param string $cpf CPF para validar
     * @return bool Verdadeiro se o CPF for válido
     */
    public static function isValidCpf($cpf)
    {
        // Remove formatação
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Algoritmo de validação do CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida um CNPJ
     * 
     * @param string $cnpj CNPJ para validar
     * @return bool Verdadeiro se o CNPJ for válido
     */
    public static function isValidCnpj($cnpj)
    {
        // Remove formatação
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Algoritmo de validação do CNPJ
        $sum = 0;
        $weight = 5;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }
        $result = $sum % 11;
        if ($cnpj[12] != ($result < 2 ? 0 : 11 - $result)) {
            return false;
        }

        $sum = 0;
        $weight = 6;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }
        $result = $sum % 11;

        return $cnpj[13] == ($result < 2 ? 0 : 11 - $result);
    }

    /**
     * Sanitiza uma string
     * 
     * @param string $str String para sanitizar
     * @return string String sanitizada
     */
    public static function sanitizeString($str)
    {
        // Substituição para FILTER_SANITIZE_STRING
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza um número inteiro
     * 
     * @param mixed $value Valor para sanitizar
     * @return int Inteiro sanitizado
     */
    public static function sanitizeInt($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitiza um número float
     * 
     * @param mixed $value Valor para sanitizar
     * @return float Float sanitizado
     */
    public static function sanitizeFloat($value)
    {
        $value = str_replace(',', '.', $value);
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
}
