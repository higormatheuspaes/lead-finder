<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasLabel
{
    case Novo = 'novo';
    case Qualificado = 'qualificado';
    case Descartado = 'descartado';
    case NaFila = 'na_fila';
    case Enviado = 'enviado';
    case Entregue = 'entregue';
    case Lido = 'lido';
    case Falhou = 'falhou';
    case Respondeu = 'respondeu';
    case OptOut = 'opt_out';
    case Convertido = 'convertido';

    /** Status que o coletor ainda pode requalificar (fora do funil de envio). */
    public const REQUALIFIABLE = [self::Novo, self::Qualificado, self::Descartado];

    public function getLabel(): string
    {
        return match ($this) {
            self::Novo => 'Novo',
            self::Qualificado => 'Qualificado',
            self::Descartado => 'Descartado',
            self::NaFila => 'Na fila',
            self::Enviado => 'Enviado',
            self::Entregue => 'Entregue',
            self::Lido => 'Lido',
            self::Falhou => 'Falhou',
            self::Respondeu => 'Respondeu',
            self::OptOut => 'Opt-out',
            self::Convertido => 'Convertido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Novo, self::Descartado => 'gray',
            self::Qualificado, self::Entregue, self::Lido => 'info',
            self::NaFila, self::Enviado => 'warning',
            self::Falhou, self::OptOut => 'danger',
            self::Respondeu, self::Convertido => 'success',
        };
    }
}
