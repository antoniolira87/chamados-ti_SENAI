<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida um CPF já normalizado (somente os 11 números).
 * Confere a sequência repetida e os dois dígitos verificadores.
 */
class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::valido((string) $value)) {
            $fail('O CPF informado é inválido. Confira os números digitados.');
        }
    }

    public static function valido(string $cpf): bool
    {
        if (! preg_match('/^\d{11}$/', $cpf)) {
            return false;
        }

        // 000.000.000-00, 111.111.111-11... passam na conta, mas não existem.
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        return substr($cpf, 9, 2) === self::digitos(substr($cpf, 0, 9));
    }

    /** Calcula os dois dígitos verificadores a partir dos 9 primeiros números. */
    public static function digitos(string $base): string
    {
        foreach ([10, 11] as $pesoInicial) {
            $soma = 0;
            foreach (str_split($base) as $i => $numero) {
                $soma += (int) $numero * ($pesoInicial - $i);
            }
            $resto = $soma % 11;
            $base .= $resto < 2 ? '0' : (string) (11 - $resto);
        }

        return substr($base, -2);
    }

    /** Gera um CPF válido (usado nos dados de exemplo e nos testes). */
    public static function gerar(): string
    {
        do {
            $base = str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $cpf = $base.self::digitos($base);
        } while (! self::valido($cpf));

        return $cpf;
    }

    public static function formatar(?string $cpf): string
    {
        return $cpf && strlen($cpf) === 11
            ? substr($cpf, 0, 3).'.'.substr($cpf, 3, 3).'.'.substr($cpf, 6, 3).'-'.substr($cpf, 9, 2)
            : (string) $cpf;
    }
}
