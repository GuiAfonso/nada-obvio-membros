<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    private const CATEGORIA_COLORS = [
        'default' => 'Cinza claro',
        'gray' => 'Cinza',
        'brown' => 'Marrom',
        'orange' => 'Laranja',
        'yellow' => 'Amarelo',
        'green' => 'Verde',
        'blue' => 'Azul',
        'purple' => 'Roxo',
        'pink' => 'Rosa',
        'red' => 'Vermelho',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required(),
                TextInput::make('categoria'),
                Select::make('categoria_color')
                    ->label('Cor da categoria')
                    ->options(self::CATEGORIA_COLORS)
                    ->default('default'),
                TextInput::make('cidade'),
                TextInput::make('whatsapp')
                    ->tel(),
            ]);
    }
}
