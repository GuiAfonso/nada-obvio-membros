<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->dehydrateStateUsing(fn (string $state) => Hash::make($state))
                    ->visibleOn('create')
                    ->helperText('Deixe em branco para gerar acesso apenas via webhook da Hotmart.'),
                TextInput::make('hotmart_transaction')
                    ->label('Transação Hotmart')
                    ->disabled(),
                Toggle::make('ativo')
                    ->label('Acesso ativo'),
                Toggle::make('is_admin')
                    ->label('Administrador (acesso ao painel)'),
            ]);
    }
}
