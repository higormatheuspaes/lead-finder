<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WebsiteType: string implements HasColor, HasLabel
{
    case None = 'none';
    case Social = 'social';
    case Site = 'site';

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'Sem site',
            self::Social => 'Rede social',
            self::Site => 'Site próprio',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::None => 'danger',
            self::Social => 'warning',
            self::Site => 'success',
        };
    }
}
