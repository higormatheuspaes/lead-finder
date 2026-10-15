<?php

namespace App\Support;

class Phone
{
    /**
     * Aceita o telefone internacional do Google ("+55 47 99122-6162") ou o digitado no painel
     * sem "+", que é tratado como brasileiro ("47 99647-5752").
     *
     * shortcut: só entende números do Brasil (+55); estrangeiros viram null. Ampliar quando prospectar fora.
     *
     * @return array{0: ?string, 1: bool} [e164 sem "+", é celular?]
     */
    public static function normalize(?string $phone): array
    {
        $phone = trim((string) $phone);
        $digits = preg_replace('/\D/', '', str_starts_with($phone, '+') ? $phone : "55{$phone}");

        if (! str_starts_with($digits, '55')) {
            return [null, false];
        }

        $national = substr($digits, 2); // DDD + número

        return match (true) {
            strlen($national) === 11 && $national[2] === '9' => [$digits, true],  // (47) 9xxxx-xxxx
            strlen($national) === 10 => [$digits, false],                         // (47) 3xxx-xxxx
            default => [null, false],
        };
    }
}
