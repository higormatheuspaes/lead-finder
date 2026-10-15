<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Support\Phone;
use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    // Só o que faz sentido editar à mão; o resto vem do Google.
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('greeting_name')
                    ->label('Nome de saudação')
                    ->helperText('Vai no "Olá, {nome}!" da mensagem do WhatsApp.')
                    ->maxLength(255),
                TextInput::make('phone_raw')
                    ->label('Telefone')
                    ->helperText('Com DDD, ex.: 47 99647-5752. Para número de fora do Brasil, comece com +.')
                    ->required()
                    ->maxLength(30)
                    ->rule(fn () => function (string $attribute, mixed $value, Closure $fail) {
                        if (Phone::normalize((string) $value)[0] === null) {
                            $fail('Telefone inválido. Use DDD + número, ex.: 47 99647-5752.');
                        }
                    }),
                Textarea::make('notes')->label('Notas')->rows(5)->columnSpanFull(),
            ]);
    }
}
