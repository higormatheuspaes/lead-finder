<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    #[DataProvider('numbers')]
    public function test_normalizes_international_numbers(?string $input, ?string $e164, bool $isMobile): void
    {
        $this->assertSame([$e164, $isMobile], Phone::normalize($input));
    }

    public static function numbers(): array
    {
        return [
            'celular' => ['+55 47 99122-6162', '5547991226162', true],
            'fixo' => ['+55 47 3322-1100', '554733221100', false],
            'estrangeiro' => ['+1 212-555-0199', null, false],
            'vazio' => [null, null, false],
            'incompleto' => ['+55 47 9912', null, false],
        ];
    }
}
